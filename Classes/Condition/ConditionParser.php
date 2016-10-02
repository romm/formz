<?php
/*
 * 2016 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Formz project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\Condition;

use Romm\Formz\Condition\Node\AbstractNode;
use Romm\Formz\Condition\Node\BooleanNode;
use Romm\Formz\Condition\Node\ConditionNode;
use Romm\Formz\Condition\Node\NodeFactory;
use Romm\Formz\Condition\Processor\AbstractProcessor;
use Romm\Formz\Configuration\Form\Condition\ActivationInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Result;

/**
 * A parser capable of parsing a validation condition string from a field
 * configuration, by creating a tree containing nodes which represents the
 * logical operations.
 *
 * Parsing errors are handled, and stored in `$this->result`. When a condition
 * has been parser, it is highly recommended to check if the result contains
 * errors before using the tree.
 *
 * Below is a list of what is currently supported by the parser:
 *  - Logical AND: defined by `&&`, it is no more than a logical "and" operator.
 *  - Logical OR: defined by `||`, same as above.
 *  - Operation groups: you can group several operation between parenthesis:
 *    `(...)`.
 *  - Condition names: represented by the items names in the `Activation`
 *    instance, there real meaning is a boolean result.
 */
class ConditionParser
{

    const LOGICAL_AND = '&&';
    const LOGICAL_OR = '||';

    /**
     * @var ConditionParser[]
     */
    private static $conditionParserRepository = [];

    /**
     * @var AbstractProcessor
     */
    protected $processor;

    /**
     * @var ActivationInterface
     */
    protected $condition;

    /**
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @var Result
     */
    protected $result;

    /**
     * @var AbstractNode
     */
    protected $node;

    /**
     * Constructor.
     *
     * @param ActivationInterface $condition
     * @param AbstractProcessor   $processor
     */
    protected function __construct(ActivationInterface $condition, AbstractProcessor $processor = null)
    {
        $splitCondition = $this->splitConditionExpression($condition->getCondition());

        $this->condition = $condition;
        $this->processor = $processor;
        $this->nodeFactory = GeneralUtility::makeInstance(NodeFactory::class, $this->processor);
        $this->result = GeneralUtility::makeInstance(Result::class);

        $this->node = $this->getNodeRecursive($splitCondition);
    }

    /**
     * General function to parse a condition. Needs a condition instance, and a
     * condition processor. Returns an instance of a parser, which gives access
     * to the functions `getResult()` and `getTree()`.
     *
     * @param ActivationInterface $condition The condition instance.
     * @param AbstractProcessor   $processor The condition processor.
     * @return ConditionParser
     */
    public static function parse(ActivationInterface $condition, AbstractProcessor $processor = null)
    {
        $hash = sha1(serialize([get_class($processor), $condition->getCondition(), $condition->getItems()]));

        if (false === isset(self::$conditionParserRepository[$hash])) {
            /** @var ConditionParser $conditionParser */
            $conditionParser = new ConditionParser($condition, $processor);

            self::$conditionParserRepository[$hash] = $conditionParser;
        }

        return self::$conditionParserRepository[$hash];
    }

    /**
     * Recursive function to convert an array of condition data to a tree of
     * nodes.
     *
     * @param array $splitCondition
     * @return AbstractNode|null
     */
    protected function getNodeRecursive(array &$splitCondition)
    {
        $node = null;
        $leftNode = null;
        $operator = null;
        $expression = $splitCondition;
        $lastOr = null;

        while (false === empty($expression)) {
            if ($this->result->hasErrors()) {
                break;
            }

            switch ($expression[0]) {
                case ')':
                    $this->result->addError(new Error('Parenthesis closes invalid group.', 1457969163));
                    break;
                case '(':
                    $groupNode = $this->getGroupNode($expression);
                    $expression = array_slice($expression, count($groupNode) + 2);
                    $node = $this->getNodeRecursive($groupNode);
                    break;
                case ConditionParser::LOGICAL_OR:
                case ConditionParser::LOGICAL_AND:
                    if (null === $node) {
                        $this->result->addError(new Error('Logical operator must be preceded by a valid operation.', 1457544986));
                    } else {
                        $operator = $expression[0];

                        if (ConditionParser::LOGICAL_OR === $operator) {
                            if (null !== $lastOr) {
                                $node = $this->nodeFactory->getNode(
                                    BooleanNode::class,
                                    [$lastOr, $node, $operator]
                                );
                            }
                            $lastOr = $node;
                        } else {
                            $leftNode = $node;
                            $node = null;
                        }

                        array_shift($expression);
                    }
                    break;
                default:
                    $conditionName = $expression[0];
                    if (false === $this->condition->hasItem($conditionName)) {
                        $this->result->addError(new Error('The condition "' . $conditionName . '" does not exist.', 1457628378));
                    } else {
                        $node = $this->nodeFactory->getNode(
                            ConditionNode::class,
                            [
                                $conditionName,
                                $this->condition->getItem($conditionName)
                            ]
                        );
                        array_shift($expression);
                    }
                    break;
            }

            if (null !== $leftNode
                && null !== $node
            ) {
                $node = $this->nodeFactory->getNode(
                    BooleanNode::class,
                    [$leftNode, $node, $operator]
                );

                $leftNode = null;
                $operator = null;
            }
        }

        if (null !== $leftNode) {
            $this->result->addError(new Error('Logical operator must be followed by a valid operation.', 1457545071));
        } elseif (null !== $lastOr) {
            $node = $this->nodeFactory->getNode(
                BooleanNode::class,
                [$lastOr, $node, ConditionParser::LOGICAL_OR]
            );
        }

        return $node;
    }

    /**
     * Will fetch for a group of operations in a given array: the first item
     * must be a parenthesis. If its closing parenthesis is found, then the
     * inner part of the group is returned. Example:
     *
     * Input: (cond1 && (cond2 || cond3)) && cond4
     * Output: cond1 && (cond2 || cond3)
     *
     * @param array $splitCondition
     * @return array
     */
    protected function getGroupNode(array $splitCondition)
    {
        $parenthesis = 1;
        $index = 0;
        while ($parenthesis > 0) {
            $index++;
            if ($index > count($splitCondition)) {
                $parenthesis = -1;
                break;
            }

            if ('(' === $splitCondition[$index]) {
                $parenthesis++;
            }
            if (')' === $splitCondition[$index]) {
                $parenthesis--;
            }
        }

        $finalSplitCondition = [];
        if (-1 === $parenthesis) {
            $this->result->addError(new Error('Parenthesis not correctly closed.', 1457544856));
        } else {
            for ($i = 1; $i < $index; $i++) {
                $finalSplitCondition[] = $splitCondition[$i];
            }
        }

        return $finalSplitCondition;
    }

    /**
     * Will split a condition expression string in an exploded array where each
     * entry represents an operation.
     *
     * @param string $condition
     * @return array
     */
    protected function splitConditionExpression($condition)
    {
        preg_match_all('/(\w+|\(|\)|\&\&|\|\|)/', trim($condition), $result);

        return $result[0];
    }

    /**
     * @return AbstractNode
     */
    public function getTree()
    {
        return $this->node;
    }

    /**
     * @return Result
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * When this instance is saved in TYPO3 cache, we need not to store all the
     * properties to increase performance.
     *
     * @return array
     */
    public function __sleep()
    {
        return ['nodeFactory', 'result', 'node'];
    }

    /**
     * When an instance of this class is fetched from cache, we need to inject
     * the needed properties which were not saved thanks to the `__sleep()`
     * function.
     *
     * @param AbstractProcessor   $processor
     * @param ActivationInterface $condition
     */
    public function injectObjects(AbstractProcessor $processor, ActivationInterface $condition)
    {
        $this->processor = $processor;
        $this->condition = $condition;
        $this->nodeFactory->setProcessor($processor);
    }
}
