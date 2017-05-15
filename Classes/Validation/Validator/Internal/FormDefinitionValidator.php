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

namespace Romm\Formz\Validation\Validator\Internal;

use Romm\Formz\Condition\Exceptions\InvalidConditionException;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\Definition\FormDefinition;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class FormDefinitionValidator extends AbstractValidator
{
    /**
     * @var FormDefinition
     */
    protected $definition;

    /**
     * @param FormDefinition $definition
     */
    public function isValid($definition)
    {
        $this->definition = $definition;

        $this->validateConditionList();
        $this->validateFieldsConditions();
    }

    /**
     * Validates each condition of the list, at the root of the form definition.
     */
    protected function validateConditionList()
    {
        foreach ($this->definition->getConditionList() as $conditionName => $condition) {
            try {
                $condition->validateConditionConfiguration($this->definition);
            } catch (InvalidConditionException $exception) {
                $property = "conditionList.{$conditionName}";
                $this->addPropertyError($property, $exception->getMessage(), $exception->getCode());
            }
        }
    }

    /**
     * Loops on each field, and validates every condition.
     */
    protected function validateFieldsConditions()
    {
        foreach ($this->definition->getFields() as $field) {
            $this->validateFieldsValidatorsConditions($field);

            if (false === $field->hasActivation()) {
                continue;
            }

            foreach ($field->getActivation()->getConditions() as $conditionName => $condition) {
                try {
                    $condition->validateConditionConfiguration($this->definition);
                } catch (InvalidConditionException $exception) {
                    $property = "fields.{$field->getName()}.activation.conditions.{$conditionName}";
                    $this->addPropertyError($property, $exception->getMessage(), $exception->getCode());
                }
            }
        }
    }

    /**
     * Loops on each field validator, and validates every condition.
     *
     * @param Field $field
     */
    protected function validateFieldsValidatorsConditions(Field $field)
    {
        foreach ($field->getValidators() as $validator) {
            if (false === $validator->hasActivation()) {
                continue;
            }

            foreach ($validator->getActivation()->getConditions() as $conditionName => $condition) {
                try {
                    $condition->validateConditionConfiguration($this->definition);
                } catch (InvalidConditionException $exception) {
                    $property = "fields.{$field->getName()}.validation.{$validator->getName()}.activation.conditions.{$conditionName}";
                    $this->addPropertyError($property, $exception->getMessage(), $exception->getCode());
                }
            }
        }
    }

    /**
     * @param string $property
     * @param string $message
     * @param int    $code
     */
    protected function addPropertyError($property, $message, $code)
    {
        $error = new Error($message, $code);
        $this->result->forProperty($property)->addError($error);
    }
}
