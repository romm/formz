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

use Romm\Formz\Condition\Parser\Node\NodeInterface;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Contains several information of an expression scope for the condition parser.
 */
class ConditionParserScope implements SingletonInterface
{
    /**
     * @var array
     */
    private $expression;

    /**
     * @var NodeInterface
     */
    private $node;

    /**
     * @var NodeInterface
     */
    private $lastOrNode;

    /**
     * @var NodeInterface
     */
    private $currentLeftNode;

    /**
     * @var string
     */
    private $currentOperator;

    /**
     * @return array
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param array $expression
     * @return $this
     */
    public function setExpression(array $expression)
    {
        $this->expression = $expression;

        return $this;
    }

    /**
     * @return $this
     */
    public function shiftExpression()
    {
        array_shift($this->expression);

        return $this;
    }

    /**
     * @return NodeInterface
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @param NodeInterface $node
     * @return $this
     */
    public function setNode(NodeInterface $node)
    {
        $this->node = $node;

        return $this;
    }

    /**
     * @return $this
     */
    public function deleteNode()
    {
        $this->node = null;

        return $this;
    }

    /**
     * @return NodeInterface
     */
    public function getLastOrNode()
    {
        return $this->lastOrNode;
    }

    /**
     * @param NodeInterface $lastOrNode
     * @return $this
     */
    public function setLastOrNode(NodeInterface $lastOrNode)
    {
        $this->lastOrNode = $lastOrNode;

        return $this;
    }

    /**
     * @return NodeInterface
     */
    public function getCurrentLeftNode()
    {
        return $this->currentLeftNode;
    }

    /**
     * @param NodeInterface $currentLeftNode
     * @return $this
     */
    public function setCurrentLeftNode(NodeInterface $currentLeftNode)
    {
        $this->currentLeftNode = $currentLeftNode;

        return $this;
    }

    /**
     * @return $this
     */
    public function deleteCurrentLeftNode()
    {
        $this->currentLeftNode = null;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentOperator()
    {
        return $this->currentOperator;
    }

    /**
     * @param string $currentOperator
     * @return $this
     */
    public function setCurrentOperator($currentOperator)
    {
        $this->currentOperator = $currentOperator;

        return $this;
    }

    /**
     * @return $this
     */
    public function deleteCurrentOperator()
    {
        $this->currentOperator = null;

        return $this;
    }
}
