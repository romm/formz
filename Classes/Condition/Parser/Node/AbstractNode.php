<?php
/*
 * 2017 Romain CANON <romain.hydrocanon@gmail.com>
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

use Romm\Formz\Condition\Parser\ConditionTree;

abstract class AbstractNode implements NodeInterface
{
    /**
     * @var NodeInterface
     */
    protected $parent;

    /**
     * @var ConditionTree
     */
    protected $tree;

    /**
     * @return NodeInterface
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param NodeInterface $parent
     */
    public function setParent(NodeInterface $parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return ConditionTree
     */
    public function getTree()
    {
        return ($this->tree) ?: $this->parent->getTree();
    }

    /**
     * @param ConditionTree $tree
     */
    public function setTree(ConditionTree $tree)
    {
        $this->tree = $tree;
    }

    /**
     * @inheritdoc
     */
    public function along(callable $callback)
    {
        call_user_func($callback, $this);
    }

    /**
     * @param mixed $value
     * @return array
     */
    protected function toArray($value)
    {
        return (is_array($value))
            ? $value
            : [$value];
    }
}
