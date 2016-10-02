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

use Romm\Formz\Configuration\Form\Field\Field;

/**
 * The condition node global interface.
 */
interface NodeInterface
{

    /**
     * Binds a node factory to this node.
     *
     * @param NodeFactory $nodeFactory
     * @return $this
     */
    public function setNodeFactory(NodeFactory $nodeFactory);

    /**
     * Will return the result for a given field name, depending on the processor
     * bound to this node.
     *
     * @param Field $field
     * @return string|bool
     */
    public function getResult(Field $field);

    /**
     * CSS implementation for `getResult()`.
     *
     * @param Field $field
     * @return mixed
     */
    public function getCssResult(Field $field);

    /**
     * JavaScript implementation for `getResult()`.
     *
     * @param Field $field
     * @return mixed
     */
    public function getJavaScriptResult(Field $field);

    /**
     * PHP implementation for `getResult()`.
     *
     * @param Field $field
     * @return mixed
     */
    public function getPhpResult(Field $field);
}
