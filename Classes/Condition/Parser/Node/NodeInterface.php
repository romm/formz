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

namespace Romm\Formz\Condition\Parser\Node;

use Romm\Formz\Condition\Parser\ConditionTree;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;

/**
 * The condition node global interface.
 */
interface NodeInterface
{
    /**
     * @return NodeInterface
     */
    public function getParent();

    /**
     * @param self $parent
     */
    public function setParent(self $parent);

    /**
     * @return ConditionTree
     */
    public function getTree();

    /**
     * @param ConditionTree $tree
     */
    public function setTree(ConditionTree $tree);

    /**
     * Allows to go through all the nodes and their sub-nodes. The callback is
     * called for every node, with a unique argument: the node instance.
     *
     * @param callable $callback
     */
    public function along(callable $callback);

    /**
     * CSS implementation of the node.
     *
     * @return mixed
     */
    public function getCssResult();

    /**
     * JavaScript implementation of the node.
     *
     * @return mixed
     */
    public function getJavaScriptResult();

    /**
     * PHP implementation of the node.
     *
     * @param PhpConditionDataObject $dataObject
     * @return bool
     */
    public function getPhpResult(PhpConditionDataObject $dataObject);
}
