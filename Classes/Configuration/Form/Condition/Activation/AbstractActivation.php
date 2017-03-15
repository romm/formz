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

namespace Romm\Formz\Configuration\Form\Condition\Activation;

use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorInterface;
use Romm\ConfigurationObject\Service\Items\Parents\ParentsTrait;
use Romm\Formz\Condition\Items\ConditionItemInterface;
use Romm\Formz\Configuration\AbstractFormzConfiguration;
use Romm\Formz\Configuration\Form\Form;
use Romm\Formz\Exceptions\EntryNotFoundException;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Utility\ArrayUtility;

abstract class AbstractActivation extends AbstractFormzConfiguration implements ActivationInterface, DataPreProcessorInterface
{
    use ParentsTrait;

    /**
     * @var string
     */
    protected $expression;

    /**
     * @var ConditionItemInterface[]
     * @mixedTypesResolver \Romm\Formz\Configuration\Form\Condition\ConditionItemResolver
     */
    protected $conditions = [];

    /**
     * @var ActivationUsageInterface
     */
    private $rootObject;

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param string $expression
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
    }

    /**
     * Will merge the items with the ones from the `$activationCondition`
     * property of the root form configuration.
     *
     * @return ConditionItemInterface[]
     */
    public function getConditions()
    {
        $activationCondition = $this->withFirstParent(
            Form::class,
            function (Form $formConfiguration) {
                return $formConfiguration->getActivationCondition();
            }
        );
        $activationCondition = ($activationCondition) ?: [];

        return ArrayUtility::arrayMergeRecursiveOverrule($activationCondition, $this->conditions);
    }

    /**
     * @param string $name Name of the item.
     * @return bool
     */
    public function hasCondition($name)
    {
        $conditions = $this->getConditions();

        return true === isset($conditions[$name]);
    }

    /**
     * Return the item with the given name.
     *
     * @param string $name Name of the item.
     * @return ConditionItemInterface
     * @throws EntryNotFoundException
     */
    public function getCondition($name)
    {
        if (false === $this->hasCondition($name)) {
            throw EntryNotFoundException::activationConditionNotFound($name);
        }

        $items = $this->getConditions();

        return $items[$name];
    }

    /**
     * @param string                 $name
     * @param ConditionItemInterface $condition
     */
    public function addCondition($name, ConditionItemInterface $condition)
    {
        $this->conditions[$name] = $condition;
    }

    /**
     * @return ActivationUsageInterface
     */
    public function getRootObject()
    {
        return $this->rootObject;
    }

    /**
     * @param ActivationUsageInterface $rootObject
     */
    public function setRootObject(ActivationUsageInterface $rootObject)
    {
        $this->rootObject = $rootObject;
    }

    /**
     * @param DataPreProcessor $processor
     */
    public static function dataPreProcessor(DataPreProcessor $processor)
    {
        $data = $processor->getData();

        if (isset($data['condition'])) {
            $error = new Error(
                'The property "condition" has been deprecated and renamed to "expression", please change your TypoScript configuration.',
                1488483802
            );
            $processor->addError($error);
        }
        if (isset($data['items'])) {
            $error = new Error(
                'The property "items" has been deprecated and renamed to "conditions", please change your TypoScript configuration.',
                1488531112
            );
            $processor->addError($error);
        }
    }
}
