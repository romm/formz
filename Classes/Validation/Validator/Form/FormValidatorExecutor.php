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

namespace Romm\Formz\Validation\Validator\Form;

use Romm\Formz\Behaviours\BehavioursManager;
use Romm\Formz\Condition\Processor\ConditionProcessor;
use Romm\Formz\Condition\Processor\ConditionProcessorFactory;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\Definition\Field\Validation\Validator;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Service\MessageService;
use Romm\Formz\Validation\DataObject\ValidatorDataObject;
use Romm\Formz\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;

class FormValidatorExecutor
{
    /**
     * @var FormObject
     */
    protected $formObject;

    /**
     * @var ConditionProcessor
     */
    private $conditionProcessor;

    /**
     * @var array
     */
    protected $fieldsActivationChecked = [];

    /**
     * Current queue, used to prevent infinite loop.
     *
     * @var array
     */
    protected $fieldsActivationChecking = [];

    /**
     * @var array
     */
    protected $fieldsValidated = [];

    /**
     * Array of arbitral data which are handled by validators.
     *
     * @var array
     */
    protected $validationData = [];

    /**
     * @var PhpConditionDataObject
     */
    protected $phpConditionDataObject;

    /**
     * @param FormObject $formObject
     */
    public function __construct(FormObject $formObject)
    {
        $this->formObject = $formObject;
        $this->conditionProcessor = $this->getConditionProcessor();
        $this->phpConditionDataObject = $this->getPhpConditionDataObject();
    }

    /**
     * @return FormValidatorExecutor
     */
    public function applyBehaviours()
    {
        /** @var BehavioursManager $behavioursManager */
        $behavioursManager = GeneralUtility::makeInstance(BehavioursManager::class);
        $behavioursManager->applyBehaviourOnFormInstance($this->formObject);

        return $this;
    }

    /**
     * This function will take care of deactivating the validation for fields
     * that do not match their activation condition.
     *
     * @return FormValidatorExecutor
     */
    public function checkFieldsActivation()
    {
        foreach ($this->formObject->getDefinition()->getFields() as $field) {
            if (false === $this->getResult()->fieldIsDeactivated($field)) {
                $this->checkFieldActivation($field);
            }
        }

        return $this;
    }

    /**
     * @param Field $field
     */
    protected function checkFieldActivation(Field $field)
    {
        // Prevents loop checking.
        if ($this->fieldActivationIsBeingChecked($field)
            || $this->fieldActivationHasBeenChecked($field)
        ) {
            return;
        }

        $this->markFieldActivationCheckBegin($field);

        if (true === $field->hasActivation()
            && false === $this->getFieldActivationProcessResult($field)
        ) {
            $this->getResult()->deactivateField($field);
        }

        $this->checkFieldValidatorActivation($field);

        $this->markFieldActivationAsChecked($field);
        $this->markFieldActivationCheckEnd($field);
    }

    /**
     * @param Field $field
     */
    protected function checkFieldValidatorActivation(Field $field)
    {
        foreach ($field->getValidators() as $validator) {
            if (true === $validator->hasActivation()
                && false === $this->getValidatorActivationProcessResult($validator)
            ) {
                $this->getResult()->deactivateValidator($validator);
            }
        }
    }

    /**
     * @param callable $callback
     * @return FormValidatorExecutor
     */
    public function validateFields(callable $callback = null)
    {
        foreach ($this->formObject->getDefinition()->getFields() as $field) {
            $this->validateField($field);

            if ($callback
                && $this->fieldWasValidated($field)
            ) {
                call_user_func($callback, $field);
            }
        }

        return $this;
    }

    /**
     * Will loop on each validator and apply it on the field. The validation
     * result is merged with the form result.
     *
     * @param Field $field
     */
    public function validateField(Field $field)
    {
        if (false === $this->fieldWasValidated($field)) {
            $this->checkFieldActivation($field);

            if (false === $this->getResult()->fieldIsDeactivated($field)) {
                $this->markFieldAsValidated($field);

                // Looping on the field's validators settings...
                foreach ($field->getValidators() as $validator) {
                    if ($this->getResult()->validatorIsDeactivated($validator)) {
                        continue;
                    }

                    $validatorResult = $this->processFieldValidator($field, $validator);

                    // Breaking the loop if an error occurred: we stop the validation process for the current field.
                    if ($validatorResult->hasErrors()) {
                        break;
                    }
                }
            }
        }
    }

    /**
     * @param Field     $field
     * @param Validator $validator
     * @return Result
     */
    protected function processFieldValidator(Field $field, Validator $validator)
    {
        $form = $this->formObject->getForm();
        $fieldName = $field->getName();
        $fieldValue = ObjectAccess::getProperty($form, $fieldName);
        $validatorDataObject = new ValidatorDataObject($this->formObject, $validator);

        /** @var ValidatorInterface $validatorInstance */
        $validatorInstance = GeneralUtility::makeInstance(
            $validator->getClassName(),
            $validator->getOptions(),
            $validatorDataObject
        );

        $validatorResult = $validatorInstance->validate($fieldValue);
        $validatorResult = MessageService::get()->sanitizeValidatorResult($validatorResult, $validator->getName());

        if ($validatorInstance instanceof AbstractValidator
            && false === empty($validationData = $validatorInstance->getValidationData())
        ) {
            $this->validationData[$fieldName] = ($this->validationData[$fieldName]) ?: [];
            $this->validationData[$fieldName] = array_merge(
                $this->validationData[$fieldName],
                $validationData
            );

            $form->setValidationData($this->validationData);
        }

        $this->getResult()->forProperty($fieldName)->merge($validatorResult);
        unset($validatorDataObject);

        return $validatorResult;
    }

    /**
     * @return FormResult
     */
    public function getResult()
    {
        return $this->formObject->getFormResult();
    }

    /**
     * @param Field $field
     * @return bool
     */
    protected function getFieldActivationProcessResult(Field $field)
    {
        return $this->conditionProcessor->getActivationConditionTreeForField($field)->getPhpResult($this->phpConditionDataObject);
    }

    /**
     * @param Validator $validator
     * @return bool
     */
    protected function getValidatorActivationProcessResult(Validator $validator)
    {
        return $this->conditionProcessor->getActivationConditionTreeForValidator($validator)->getPhpResult($this->phpConditionDataObject);
    }

    /**
     * @param Field $field
     * @return bool
     */
    protected function fieldActivationHasBeenChecked(Field $field)
    {
        return in_array($field->getName(), $this->fieldsActivationChecked);
    }

    /**
     * @param Field $field
     */
    protected function markFieldActivationAsChecked(Field $field)
    {
        $this->fieldsActivationChecked[] = $field->getName();
    }

    /**
     * @param Field $field
     * @return bool
     */
    protected function fieldActivationIsBeingChecked(Field $field)
    {
        return isset($this->fieldsActivationChecking[$field->getName()]);
    }

    /**
     * @param Field $field
     */
    protected function markFieldActivationCheckBegin(Field $field)
    {
        $this->fieldsActivationChecking[$field->getName()] = true;
    }

    /**
     * @param Field $field
     */
    protected function markFieldActivationCheckEnd(Field $field)
    {
        unset($this->fieldsActivationChecking[$field->getName()]);
    }

    /**
     * @param Field $field
     * @return bool
     */
    protected function fieldWasValidated(Field $field)
    {
        return in_array($field->getName(), $this->fieldsValidated);
    }

    /**
     * @param Field $field
     */
    protected function markFieldAsValidated(Field $field)
    {
        $this->fieldsValidated[] = $field->getName();
    }

    /**
     * @return ConditionProcessor
     */
    protected function getConditionProcessor()
    {
        return ConditionProcessorFactory::getInstance()->get($this->formObject);
    }

    /**
     * @return PhpConditionDataObject
     */
    protected function getPhpConditionDataObject()
    {
        return new PhpConditionDataObject($this->formObject->getForm(), $this);
    }
}
