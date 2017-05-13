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

use Romm\ConfigurationObject\Service\Items\Parents\ParentsTrait;
use Romm\Formz\Condition\Items\ConditionItemInterface;
use Romm\Formz\Configuration\AbstractFormzConfiguration;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Form\Definition\FormDefinition;
use TYPO3\CMS\Core\Utility\ArrayUtility;

abstract class AbstractActivation extends AbstractFormzConfiguration implements ActivationInterface
{
    use ParentsTrait;

    /**
     * @var string
     */
    protected $expression;

    /**
     * @var \Romm\Formz\Condition\Items\ConditionItemInterface[]
     * @mixedTypesResolver \Romm\Formz\Form\Definition\Condition\ConditionItemResolver
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
     * @return ConditionItemInterface[]
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * Will merge the conditions with the condition list of the parent form.
     *
     * @return ConditionItemInterface[]
     */
    public function getAllConditions()
    {
        $conditionList = $this->withFirstParent(
            FormDefinition::class,
            function (FormDefinition $formConfiguration) {
                return $formConfiguration->getConditionList();
            }
        );

        $conditionList = ($conditionList) ?: [];
        ArrayUtility::mergeRecursiveWithOverrule($conditionList, $this->conditions);

        return $conditionList;
    }

    /**
     * @param string $name Name of the condition.
     * @return bool
     */
    public function hasCondition($name)
    {
        $conditions = $this->getAllConditions();

        return true === isset($conditions[$name]);
    }

    /**
     * Return the condition with the given name.
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

        $items = $this->getAllConditions();

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
}
