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

namespace Romm\Formz\Condition\Node;

use Romm\Formz\Condition\ConditionParser;
use Romm\Formz\Configuration\Form\Field\Field;

/**
 * A boolean node, which contains two sides and an operator.
 */
class BooleanNode extends AbstractNode
{

    /**
     * @var string
     */
    protected $operator;

    /**
     * @var AbstractNode
     */
    protected $leftSide;

    /**
     * @var AbstractNode
     */
    protected $rightSide;

    /**
     * Constructor.
     *
     * @param AbstractNode $leftSide  Left side of the boolean expression.
     * @param AbstractNode $rightSide Right side of the boolean expression.
     * @param string       $operator  One of the `ConditionParser::LOGICAL_*` constants.
     */
    public function __construct(AbstractNode $leftSide, AbstractNode $rightSide, $operator)
    {
        $this->leftSide = $leftSide;
        $this->rightSide = $rightSide;
        $this->operator = $operator;
    }

    /**
     * @inheritdoc
     */
    public function getCssResult(Field $field)
    {
        return $this->getLogicalResult($field, 'processLogicalAndCss', 'processLogicalOr');
    }

    /**
     * @inheritdoc
     */
    public function getJavaScriptResult(Field $field)
    {
        return $this->getLogicalResult($field, 'processLogicalAndJavaScript', 'processLogicalOr');
    }

    /**
     * @inheritdoc
     */
    public function getPhpResult(Field $field)
    {
        return $this->getLogicalResult($field, 'processLogicalAndPhp', 'processLogicalOrPhp');
    }

    /**
     * Global function to get the result of a logical operation, the processor
     * does not matter.
     *
     * @param Field  $field              Field instance.
     * @param string $logicalAndFunction Name of the internal function called for a logical "and" operation.
     * @param string $logicalOrFunction  Name of the internal function called for a logical "or" operation.
     * @return null
     * @throws \Exception
     */
    protected function getLogicalResult(Field $field, $logicalAndFunction, $logicalOrFunction)
    {
        switch ($this->operator) {
            case ConditionParser::LOGICAL_AND:
                $result = $this->$logicalAndFunction($field, $this->leftSide, $this->rightSide);
                break;
            case ConditionParser::LOGICAL_OR:
                $result = $this->$logicalOrFunction($field, $this->leftSide, $this->rightSide);
                break;
            default:
                throw new \Exception('The boolean node has a wrong operator: "' . $this->operator . '".', 1458150438);
        }

        return $result;
    }

    /**
     * Will process a logical "and" operation on the two given nodes. The result
     * will be an array containing all the result of the "and" operation on
     * every existing results of the two nodes.
     *
     * With CSS, it means to concatenate two condition strings. Example:
     *
     * Left  = [foo="bar"]
     * Right = [pet="dog"]
     * Result = [foo="bar"][pet="dog"]
     *
     * @param Field        $field Field instance.
     * @param AbstractNode $left  Left node instance.
     * @param AbstractNode $right Right node instance.
     * @return array
     */
    protected function processLogicalAndCss(Field $field, AbstractNode $left, AbstractNode $right)
    {
        $leftResults = $this->getCleanNodeResult($left, $field);
        $rightResults = $this->getCleanNodeResult($right, $field);

        $result = [];
        foreach ($leftResults as $leftResult) {
            foreach ($rightResults as $rightResult) {
                $result[] = $leftResult . $rightResult;
            }
        }

        return $result;
    }

    /**
     * Will process a logical "and" operation on the two given nodes. The result
     * will be an array containing all the result of the "and" operation on
     * every existing results of the two nodes.
     *
     * With JavaScript, it means adding the operator `&&` between the two
     * expressions.
     *
     * @param Field        $field Field instance.
     * @param AbstractNode $left  Left node instance.
     * @param AbstractNode $right Right node instance.
     * @return array
     */
    protected function processLogicalAndJavaScript(Field $field, AbstractNode $left, AbstractNode $right)
    {
        $leftResults = $this->getCleanNodeResult($left, $field);
        $rightResults = $this->getCleanNodeResult($right, $field);

        $result = [];
        foreach ($leftResults as $leftResult) {
            foreach ($rightResults as $rightResult) {
                $result[] = $leftResult . ' && ' . $rightResult;
            }
        }

        return $result;
    }

    /**
     * Will process a logical "or" operation on the two given nodes. The result
     * will be an array containing all the result of the "or" operation on
     * every existing results of the two nodes.
     *
     * @param Field        $field Field instance.
     * @param AbstractNode $left  Left node instance.
     * @param AbstractNode $right Right node instance.
     * @return array
     */
    protected function processLogicalOr(Field $field, AbstractNode $left, AbstractNode $right)
    {
        $leftResults = $this->getCleanNodeResult($left, $field);
        $rightResults = $this->getCleanNodeResult($right, $field);

        return array_merge($leftResults, $rightResults);
    }

    /**
     * Will process a logical "and" operation on the two given nodes. The result
     * will be an array containing all the result of the "and" operation on
     * every existing results of the two nodes.
     *
     * With JavaScript, it means that the left and the right nodes are both
     * true.
     *
     * @param Field        $field Field instance.
     * @param AbstractNode $left  Left node instance.
     * @param AbstractNode $right Right node instance.
     * @return bool
     */
    protected function processLogicalAndPhp(Field $field, AbstractNode $left, AbstractNode $right)
    {
        return ($left->getPhpResult($field) && $right->getPhpResult($field));
    }

    /**
     * Will process a logical "or" operation on the two given nodes.
     *
     * @param Field        $field Field instance.
     * @param AbstractNode $left  Left node instance.
     * @param AbstractNode $right Right node instance.
     * @return bool
     */
    protected function processLogicalOrPhp(Field $field, AbstractNode $left, AbstractNode $right)
    {
        return ($left->getPhpResult($field) || $right->getPhpResult($field));
    }

    /**
     * Will return an array, no matter what the type of the node result was
     * before.
     *
     * @param AbstractNode $node
     * @param Field        $field
     * @return array
     */
    protected function getCleanNodeResult(AbstractNode $node, Field $field)
    {
        $fieldResult = $node->getResult($field);

        return (is_array($fieldResult))
            ? $fieldResult
            : [$fieldResult];
    }
}
