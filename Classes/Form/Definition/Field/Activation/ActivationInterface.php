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

namespace Romm\Formz\Form\Definition\Field\Activation;

use Romm\Formz\Condition\Items\ConditionItemInterface;

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
    public function getExpression();

    /**
     * @param string $expression
     * @return void
     */
    public function setExpression($expression);

    /**
     * Returns the condition items.
     *
     * @return ConditionItemInterface[]
     */
    public function getConditions();

    /**
     * Returns the condition items merged list of the activation, and its
     * parents.
     *
     * @return ConditionItemInterface[]
     */
    public function getAllConditions();

    /**
     * Returns true if the given item exists.
     *
     * @param string $name Name of the item.
     * @return bool
     */
    public function hasCondition($name);

    /**
     * Return the item with the given name.
     *
     * @param string $name Name of the item.
     * @return ConditionItemInterface
     */
    public function getCondition($name);

    /**
     * @return ActivationUsageInterface
     */
    public function getRootObject();

    /**
     * @param ActivationUsageInterface $rootObject
     * @return void
     */
    public function setRootObject(ActivationUsageInterface $rootObject);
}
