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

namespace Romm\Formz\Condition\Parser;

use Romm\Formz\Condition\Parser\Node\BooleanNode;
use Romm\Formz\Condition\Parser\Node\ConditionNode;
use Romm\Formz\Condition\Parser\Node\NodeInterface;
use Romm\Formz\Condition\Parser\Node\NullNode;
use Romm\Formz\Configuration\Form\Condition\Activation\ActivationInterface;
use Romm\Formz\Configuration\Form\Condition\Activation\EmptyActivation;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Result;

/**
 * A parser capable of parsing a validation condition string from a field
 * configuration, by creating a tree containing nodes which represents the
 * logical operations.
 *
 * Calling the function `parse()` will return an instance of `ConditionTree`
 * which will contain the full nodes tree, as well as a result instance.
 *
 * Parsing errors are handled, and stored in the tree result instance. When a
 * condition has been parsed, it is highly recommended to check if the result
 * contains errors before using the tree.
 *
 * Below is a list of what is currently supported by the parser:
 *  - Logical AND: defined by `&&`, it is no more than a logical "and" operator.
 *  - Logical OR: defined by `||`, same as above.
 *  - Operation groups: you can group several operation between parenthesis:
 *    `(...)`.
 *  - Condition names: represented by the items names in the `Activation`
 *    instance, there real meaning is a boolean result.
 */
class ConditionParser implements SingletonInterface
{
    const LOGICAL_AND = '&&';
    const LOGICAL_OR = '||';

    /**
     * @var Result
     */
    private $result;

    /**
     * @var ActivationInterface
     */
    private $condition;

    /**
     * @var ConditionParserScope
     */
    private $scope;

    /**
     * @return ConditionParser
     */
    public static function get()
    {
        return GeneralUtility::makeInstance(self::class);
    }

    /**
     * See class documentation.
     *
     * @param ActivationInterface $condition
     * @return ConditionTree
     */
    public function parse(ActivationInterface $condition)
    {
        $this->resetParser($condition);

        $rootNode = ($condition instanceof EmptyActivation)
            ? NullNode::get()
            : $this->getNodeRecursive();

        return GeneralUtility::makeInstance(ConditionTree::class, $rootNode, $this->result);
    }

    /**
     * @param ActivationInterface $condition
     */
    private function resetParser(ActivationInterface $condition)
    {
        $this->condition = $condition;
        $this->result = GeneralUtility::makeInstance(Result::class);

        $this->scope = $this->getNewScope();
        $this->scope->setExpression($this->splitConditionExpression($condition->getCondition()));
    }

    /**
     * Recursive function to convert an array of condition data to a nodes tree.
     *
     * @return NodeInterface|null
     */
    private function getNodeRecursive()
    {
        while (false === empty($this->scope->getExpression())) {
            if ($this->result->hasErrors()) {
                break;
            }

            $currentExpression = $this->scope->getExpression();
            $this->processToken($currentExpression[0]);
            $this->processLogicalAndNode();
        }

        $this->processLastLogicalOperatorNode();

        $node = $this->scope->getNode();
        unset($this->scope);

        return $node;
    }

    /**
     * Will process a given token, which should be in the list of know tokens.
     *
     * @param string $token
     * @return $this
     */
    private function processToken($token)
    {
        switch ($token) {
            case ')':
                $this->processTokenClosingParenthesis();
                break;
            case '(':
                $this->processTokenOpeningParenthesis();
                break;
            case ConditionParser::LOGICAL_OR:
            case ConditionParser::LOGICAL_AND:
                $this->processTokenLogicalOperator($token);
                break;
            default:
                $this->processTokenCondition($token);
                break;
        }

        return $this;
    }

    /**
     * Will process the opening parenthesis token `(`.
     *
     * A new scope will be created, containing the whole tokens list, which are
     * located between the opening parenthesis and the closing one. The scope
     * will be processed in a new scope, then the result is stored and the
     * process can keep up.
     */
    private function processTokenOpeningParenthesis()
    {
        $groupNode = $this->getGroupNode($this->scope->getExpression());

        $scopeSave = $this->scope;
        $expression = array_slice($scopeSave->getExpression(), count($groupNode) + 2);
        $scopeSave->setExpression($expression);

        $this->scope = $this->getNewScope();
        $this->scope->setExpression($groupNode);

        $node = $this->getNodeRecursive();

        $this->scope = $scopeSave;
        $this->scope->setNode($node);
    }

    /**
     * Will process the closing parenthesis token `)`.
     *
     * This function should not be called, because the closing parenthesis
     * should always be handled by the opening parenthesis token handler.
     */
    private function processTokenClosingParenthesis()
    {
        $this->addError('Parenthesis closes invalid group.', 1457969163);
    }

    /**
     * Will process the logical operator tokens `&&` and `||`.
     *
     * Depending on the type of the operator, the process will change.
     *
     * @param string $operator
     */
    private function processTokenLogicalOperator($operator)
    {
        if (null === $this->scope->getNode()) {
            $this->addError('Logical operator must be preceded by a valid operation.', 1457544986);
        } else {
            if (ConditionParser::LOGICAL_OR === $operator) {
                if (null !== $this->scope->getLastOrNode()) {
                    /*
                     * If a `or` node was already registered, we create a new
                     * boolean node to join the two nodes.
                     */
                    $node = $this->getNode(
                        BooleanNode::class,
                        [$this->scope->getLastOrNode(), $this->scope->getNode(), $operator]
                    );
                    $this->scope->setNode($node);
                }

                $this->scope->setLastOrNode($this->scope->getNode());
            } else {
                $this->scope
                    ->setCurrentLeftNode($this->scope->getNode())
                    ->deleteNode();
            }

            $this->scope
                ->setCurrentOperator($operator)
                ->shiftExpression();
        }
    }

    /**
     * Will process the condition token.
     *
     * The condition must exist in the list of items of the condition.
     *
     * @param string $condition
     */
    private function processTokenCondition($condition)
    {
        if (false === $this->condition->hasItem($condition)) {
            $this->addError('The condition "' . $condition . '" does not exist.', 1457628378);
        } else {
            $node = $this->getNode(
                ConditionNode::class,
                [
                    $condition,
                    $this->condition->getItem($condition)
                ]
            );
            $this->scope
                ->setNode($node)
                ->shiftExpression();
        }
    }

    /**
     * Will check if a "logical and" node should be created, depending on which
     * tokens were processed before.
     *
     * @return $this
     */
    private function processLogicalAndNode()
    {
        if (null !== $this->scope->getCurrentLeftNode()
            && null !== $this->scope->getNode()
            && null !== $this->scope->getCurrentOperator()
        ) {
            $node = $this->getNode(
                BooleanNode::class,
                [$this->scope->getCurrentLeftNode(), $this->scope->getNode(), $this->scope->getCurrentOperator()]
            );
            $this->scope
                ->setNode($node)
                ->deleteCurrentLeftNode()
                ->deleteCurrentOperator();
        }

        return $this;
    }

    /**
     * Will check if a last logical operator node is remaining.
     *
     * @return $this
     */
    private function processLastLogicalOperatorNode()
    {
        if (null !== $this->scope->getCurrentLeftNode()) {
            $this->addError('Logical operator must be followed by a valid operation.', 1457545071);
        } elseif (null !== $this->scope->getLastOrNode()) {
            $node = $this->getNode(
                BooleanNode::class,
                [$this->scope->getLastOrNode(), $this->scope->getNode(), ConditionParser::LOGICAL_OR]
            );
            $this->scope->setNode($node);
        }

        return $this;
    }

    /**
     * @param string $nodeClassName
     * @param array  $arguments
     * @return NodeInterface
     */
    private function getNode($nodeClassName, array $arguments)
    {
        return call_user_func_array(
            [GeneralUtility::class, 'makeInstance'],
            array_merge([$nodeClassName], $arguments)
        );
    }

    /**
     * Will fetch a group of operations in a given array: the first item must be
     * a parenthesis. If its closing parenthesis is found, then the inner part
     * of the group is returned. Example:
     *
     * Input: (cond1 && (cond2 || cond3)) && cond4
     * Output: cond1 && (cond2 || cond3)
     *
     * @param array $expression
     * @return array
     */
    private function getGroupNode(array $expression)
    {
        $index = $this->getGroupNodeClosingIndex($expression);
        $finalSplitCondition = [];

        if (-1 === $index) {
            $this->addError('Parenthesis not correctly closed.', 1457544856);
        } else {
            for ($i = 1; $i < $index; $i++) {
                $finalSplitCondition[] = $expression[$i];
            }
        }

        return $finalSplitCondition;
    }

    /**
     * Returns the index of the closing parenthesis that matches the opening
     * parenthesis at index 0 of the given expression.
     *
     * @param array $expression
     * @return int
     */
    private function getGroupNodeClosingIndex(array $expression)
    {
        $parenthesis = 1;
        $index = 0;

        while ($parenthesis > 0) {
            $index++;
            if ($index > count($expression)) {
                $index = -1;
                break;
            }

            if ('(' === $expression[$index]) {
                $parenthesis++;
            } elseif (')' === $expression[$index]) {
                $parenthesis--;
            }
        }

        return $index;
    }

    /**
     * Will split a condition expression string in an exploded array where each
     * entry represents an operation.
     *
     * @param string $condition
     * @return array
     */
    private function splitConditionExpression($condition)
    {
        preg_match_all('/(\w+|\(|\)|\&\&|\|\|)/', trim($condition), $result);

        return $result[0];
    }

    /**
     * @return ConditionParserScope
     */
    private function getNewScope()
    {
        return new ConditionParserScope;
    }

    /**
     * @param string $message
     * @param int    $code
     */
    private function addError($message, $code)
    {
        $error = new Error($message, $code);
        $this->result->addError($error);
    }
}
