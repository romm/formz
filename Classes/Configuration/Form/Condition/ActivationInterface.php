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

namespace Romm\Formz\Configuration\Form\Condition;

/**
 * Interface which must be implemented by the activation classes which will be
 * used by the condition API.
 */
interface ActivationInterface
{

    /**
     * Returns the condition expression.
     *
     * @return string
     */
    public function getCondition();

    /**
     * Returns the condition items.
     *
     * @return AbstractConditionItem[]
     */
    public function getItems();

    /**
     * Returns true if the given item exists.
     *
     * @param string $itemName Name of the item.
     * @return bool
     */
    public function hasItem($itemName);

    /**
     * Return the item with the given name.
     *
     * @param string $itemName Name of the item.
     * @return AbstractConditionItem
     */
    public function getItem($itemName);
}
