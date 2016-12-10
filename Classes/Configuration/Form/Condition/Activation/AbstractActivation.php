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

namespace Romm\Formz\Configuration\Form\Condition\Activation;

use Romm\ConfigurationObject\Service\Items\Parents\ParentsTrait;
use Romm\Formz\Configuration\AbstractFormzConfiguration;
use Romm\Formz\Configuration\Form\Condition\ConditionItemResolver;
use Romm\Formz\Configuration\Form\Form;
use TYPO3\CMS\Extbase\Utility\ArrayUtility;

abstract class AbstractActivation extends AbstractFormzConfiguration implements ActivationInterface
{

    use ParentsTrait;

    /**
     * @var string
     */
    protected $condition;

    /**
     * @var \ArrayObject<Romm\Formz\Configuration\Form\Condition\ConditionItemResolver>
     * @validate NotEmpty
     */
    protected $items = [];

    /**
     * @inheritdoc
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * Will merge the items with the ones from the `$activationCondition`
     * property of the root form configuration.
     *
     * @return ConditionItemResolver[]
     */
    public function getItems()
    {
        $activationCondition = $this->withFirstParent(
            Form::class,
            function (Form $formConfiguration) {
                return $formConfiguration->getActivationCondition();
            }
        );
        $activationCondition = ($activationCondition) ?: [];

        return ArrayUtility::arrayMergeRecursiveOverrule($activationCondition, $this->items);
    }

    /**
     * @inheritdoc
     */
    public function hasItem($itemName)
    {
        $items = $this->getItems();

        return (true === isset($items[$itemName]));
    }

    /**
     * @inheritdoc
     */
    public function getItem($itemName)
    {
        if (true === $this->hasItem($itemName)) {
            $items = $this->getItems();

            return $items[$itemName];
        }

        return null;
    }
}
