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

namespace Romm\Formz\Condition\Parser\Node;

use Romm\Formz\Condition\Parser\ConditionParser;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;

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
     * @var NodeInterface
     */
    protected $leftSide;

    /**
     * @var NodeInterface
     */
    protected $rightSide;

    /**
     * Constructor.
     *
     * @param NodeInterface $leftSide  Left side of the boolean expression.
     * @param NodeInterface $rightSide Right side of the boolean expression.
     * @param string       $operator  One of the `ConditionParser::LOGICAL_*` constants.
     */
    public function __construct(NodeInterface $leftSide, NodeInterface $rightSide, $operator)
    {
        $this->leftSide = $leftSide;
        $this->rightSide = $rightSide;
        $this->operator = $operator;

        $this->leftSide->setParent($this);
        $this->rightSide->setParent($this);
    }

    /**
     * @inheritdoc
     */
    public function along(callable $callback)
    {
        $this->leftSide->along($callback);
        $this->rightSide->along($callback);
    }

    /**
     * @inheritdoc
     */
    public function getCssResult()
    {
        return $this->getLogicalResult(
            function () {
                return $this->processLogicalAndCss($this->leftSide, $this->rightSide);
            },
            function () {
                return $this->processLogicalOrCss($this->leftSide, $this->rightSide);
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function getJavaScriptResult()
    {
        return $this->getLogicalResult(
            function () {
                return $this->processLogicalAndJavaScript($this->leftSide, $this->rightSide);
            },
            function () {
                return $this->processLogicalOrJavaScript($this->leftSide, $this->rightSide);
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function getPhpResult(PhpConditionDataObject $dataObject)
    {
        return $this->getLogicalResult(
            function () use ($dataObject) {
                return $this->processLogicalAndPhp($this->leftSide, $this->rightSide, $dataObject);
            },
            function () use ($dataObject) {
                return $this->processLogicalOrPhp($this->leftSide, $this->rightSide, $dataObject);
            }
        );
    }

    /**
     * Global function to get the result of a logical operation, the processor
     * does not matter.
     *
     * @param callable $logicalAndFunction
     * @param callable $logicalOrFunction
     * @return null
     * @throws \Exception
     */
    protected function getLogicalResult(callable $logicalAndFunction, callable $logicalOrFunction)
    {
        switch ($this->operator) {
            case ConditionParser::LOGICAL_AND:
                $result = call_user_func($logicalAndFunction);
                break;
            case ConditionParser::LOGICAL_OR:
                $result = call_user_func($logicalOrFunction);
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
     * Left   = [foo="bar"]
     * Right  = [pet="dog"]
     * Result = [foo="bar"][pet="dog"]
     *
     * @param NodeInterface $left  Left node instance.
     * @param NodeInterface $right Right node instance.
     * @return array
     */
    protected function processLogicalAndCss(NodeInterface $left, NodeInterface $right)
    {
        $leftResults = $this->toArray($left->getCssResult());
        $rightResults = $this->toArray($right->getCssResult());

        $result = [];
        foreach ($leftResults as $leftResult) {
            foreach ($rightResults as $rightResult) {
                $result[] = $leftResult . $rightResult;
            }
        }

        return $result;
    }

    /**
     * Will process a logical "or" operation on the two given nodes. The result
     * will be an array containing all the result of the "or" operation on
     * every existing results of the two nodes.
     *
     * @param NodeInterface $left  Left node instance.
     * @param NodeInterface $right Right node instance.
     * @return array
     */
    protected function processLogicalOrCss(NodeInterface $left, NodeInterface $right)
    {
        return array_merge(
            $this->toArray($left->getCssResult()),
            $this->toArray($right->getCssResult())
        );
    }

    /**
     * Will process a logical "and" operation on the two given nodes. The result
     * will be an array containing all the result of the "and" operation on
     * every existing results of the two nodes.
     *
     * With JavaScript, it means adding the operator `&&` between the two
     * expressions.
     *
     * @param NodeInterface $left  Left node instance.
     * @param NodeInterface $right Right node instance.
     * @return array
     */
    protected function processLogicalAndJavaScript(NodeInterface $left, NodeInterface $right)
    {
        $leftResults = $this->toArray($left->getJavaScriptResult());
        $rightResults = $this->toArray($right->getJavaScriptResult());

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
     * @param NodeInterface $left  Left node instance.
     * @param NodeInterface $right Right node instance.
     * @return array
     */
    protected function processLogicalOrJavaScript(NodeInterface $left, NodeInterface $right)
    {
        return array_merge(
            $this->toArray($left->getJavaScriptResult()),
            $this->toArray($right->getJavaScriptResult())
        );
    }

    /**
     * Will process a logical "and" operation on the two given nodes. The result
     * will be an array containing all the result of the "and" operation on
     * every existing results of the two nodes.
     *
     * With JavaScript, it means that the left and the right nodes are both
     * true.
     *
     * @param NodeInterface          $left  Left node instance.
     * @param NodeInterface          $right Right node instance.
     * @param PhpConditionDataObject $dataObject
     * @return bool
     */
    protected function processLogicalAndPhp(NodeInterface $left, NodeInterface $right, PhpConditionDataObject $dataObject)
    {
        return $left->getPhpResult($dataObject) && $right->getPhpResult($dataObject);
    }

    /**
     * Will process a logical "or" operation on the two given nodes.
     *
     * @param NodeInterface          $left  Left node instance.
     * @param NodeInterface          $right Right node instance.
     * @param PhpConditionDataObject $dataObject
     * @return bool
     */
    protected function processLogicalOrPhp(NodeInterface $left, NodeInterface $right, PhpConditionDataObject $dataObject)
    {
        return $left->getPhpResult($dataObject) || $right->getPhpResult($dataObject);
    }
}
