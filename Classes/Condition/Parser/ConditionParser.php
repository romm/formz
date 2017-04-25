<?php
/*
 * 2017 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 FormZ project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\Condition\Parser;

use Romm\Formz\Condition\Exceptions\ConditionParserException;
use Romm\Formz\Condition\Parser\Node\BooleanNode;
use Romm\Formz\Condition\Parser\Node\ConditionNode;
use Romm\Formz\Condition\Parser\Node\NodeInterface;
use Romm\Formz\Condition\Parser\Node\NullNode;
use Romm\Formz\Form\Definition\Field\Activation\ActivationInterface;
use Romm\Formz\Form\Definition\Field\Activation\EmptyActivation;
use Romm\Formz\Service\Traits\SelfInstantiateTrait;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Result;

/**
 * A parser capable of parsing a validation condition string from a field
 * configuration, by creating a tree containing nodes that represent the
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
    use SelfInstantiateTrait;

    const LOGICAL_AND = '&&';
    const LOGICAL_OR = '||';

    const ERROR_CODE_INVALID_CLOSING_PARENTHESIS = 1457969163;
    const ERROR_CODE_CLOSING_PARENTHESIS_NOT_FOUND = 1457544856;
    const ERROR_CODE_CONDITION_NOT_FOUND = 1457628378;
    const ERROR_CODE_LOGICAL_OPERATOR_PRECEDED = 1457544986;
    const ERROR_CODE_LOGICAL_OPERATOR_FOLLOWED = 1457545071;

    /**
     * @var Result
     */
    protected $result;

    /**
     * @var ActivationInterface
     */
    protected $condition;

    /**
     * @var ConditionParserScope
     */
    protected $scope;

    /**
     * See class documentation.
     *
     * @param ActivationInterface $condition
     * @return ConditionTree
     */
    public function parse(ActivationInterface $condition)
    {
        $rootNode = null;
        $this->resetParser($condition);

        if (false === $condition instanceof EmptyActivation) {
            try {
                $rootNode = $this->getNodeRecursive();
            } catch (ConditionParserException $exception) {
                $error = new Error($exception->getMessage(), $exception->getCode());
                $this->result->addError($error);
            }
        }

        $rootNode = $rootNode ?: NullNode::get();

        /** @var ConditionTree $tree */
        $tree = GeneralUtility::makeInstance(ConditionTree::class, $rootNode, $this->result);

        return $tree;
    }

    /**
     * @param ActivationInterface $condition
     */
    protected function resetParser(ActivationInterface $condition)
    {
        $this->condition = $condition;
        $this->result = GeneralUtility::makeInstance(Result::class);

        $this->scope = $this->getNewScope();
        $this->scope->setExpression($this->splitConditionExpression($condition->getExpression()));
    }

    /**
     * Recursive function to convert an array of condition data to a nodes tree.
     *
     * @return NodeInterface|null
     */
    protected function getNodeRecursive()
    {
        while (false === empty($this->scope->getExpression())) {
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
    protected function processToken($token)
    {
        switch ($token) {
            case ')':
                $this->processTokenClosingParenthesis();
                break;
            case '(':
                $this->processTokenOpeningParenthesis();
                break;
            case self::LOGICAL_OR:
            case self::LOGICAL_AND:
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
    protected function processTokenOpeningParenthesis()
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
    protected function processTokenClosingParenthesis()
    {
        $this->addError('Parenthesis closes invalid group.', self::ERROR_CODE_INVALID_CLOSING_PARENTHESIS);
    }

    /**
     * Will process the logical operator tokens `&&` and `||`.
     *
     * Depending on the type of the operator, the process will change.
     *
     * @param string $operator
     */
    protected function processTokenLogicalOperator($operator)
    {
        if (null === $this->scope->getNode()) {
            $this->addError('Logical operator must be preceded by a valid operation.', self::ERROR_CODE_LOGICAL_OPERATOR_PRECEDED);
        } else {
            if (self::LOGICAL_OR === $operator) {
                if (null !== $this->scope->getLastOrNode()) {
                    /*
                     * If a `or` node was already registered, we create a new
                     * boolean node to join the two nodes.
                     */
                    $node = new BooleanNode($this->scope->getLastOrNode(), $this->scope->getNode(), $operator);
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
    protected function processTokenCondition($condition)
    {
        if (false === $this->condition->hasCondition($condition)) {
            $this->addError('The condition "' . $condition . '" does not exist.', self::ERROR_CODE_CONDITION_NOT_FOUND);
        } else {
            $node = new ConditionNode($condition, $this->condition->getCondition($condition));
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
    protected function processLogicalAndNode()
    {
        if (null !== $this->scope->getCurrentLeftNode()
            && null !== $this->scope->getNode()
            && null !== $this->scope->getCurrentOperator()
        ) {
            $node = new BooleanNode($this->scope->getCurrentLeftNode(), $this->scope->getNode(), $this->scope->getCurrentOperator());
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
    protected function processLastLogicalOperatorNode()
    {
        if (null !== $this->scope->getCurrentLeftNode()) {
            $this->addError('Logical operator must be followed by a valid operation.', self::ERROR_CODE_LOGICAL_OPERATOR_FOLLOWED);
        } elseif (null !== $this->scope->getLastOrNode()) {
            $node = new BooleanNode($this->scope->getLastOrNode(), $this->scope->getNode(), self::LOGICAL_OR);
            $this->scope->setNode($node);
        }

        return $this;
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
    protected function getGroupNode(array $expression)
    {
        $index = $this->getGroupNodeClosingIndex($expression);
        $finalSplitCondition = [];

        for ($i = 1; $i < $index; $i++) {
            $finalSplitCondition[] = $expression[$i];
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
    protected function getGroupNodeClosingIndex(array $expression)
    {
        $parenthesis = 1;
        $index = 0;

        while ($parenthesis > 0) {
            $index++;
            if ($index > count($expression)) {
                $this->addError('Parenthesis not correctly closed.', self::ERROR_CODE_CLOSING_PARENTHESIS_NOT_FOUND);
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
    protected function splitConditionExpression($condition)
    {
        preg_match_all('/(\w+|\(|\)|\&\&|\|\|)/', trim($condition), $result);

        return $result[0];
    }

    /**
     * @return ConditionParserScope
     */
    protected function getNewScope()
    {
        return new ConditionParserScope;
    }

    /**
     * @param string $message
     * @param int    $code
     * @throws ConditionParserException
     */
    protected function addError($message, $code)
    {
        throw new ConditionParserException($message, $code);
    }
}
