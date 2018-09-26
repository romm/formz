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

namespace Romm\Formz\Validation\Form;

use Romm\Formz\Behaviours\BehavioursManager;
use Romm\Formz\Condition\Processor\ConditionProcessor;
use Romm\Formz\Condition\Processor\ConditionProcessorFactory;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use Romm\Formz\Core\Core;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\Definition\Field\Validation\Validator;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Service\MessageService;
use Romm\Formz\Validation\Field\DataObject\ValidatorDataObject;
use Romm\Formz\Validation\Form\DataObject\FormValidatorDataObject;
use Romm\Formz\Validation\Form\Service\SubstepValidationService;
use Romm\Formz\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;

class FormValidatorExecutor
{
    /**
     * @var FormValidatorDataObject
     */
    protected $dataObject;

    /**
     * @var ConditionProcessor
     */
    private $conditionProcessor;

    /**
     * @var PhpConditionDataObject
     */
    protected $phpConditionDataObject;

    /**
     * @var SubstepValidationService
     */
    protected $substepService;

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
     * @param FormValidatorDataObject $dataObject
     */
    public function __construct(FormValidatorDataObject $dataObject)
    {
        $this->dataObject = $dataObject;

        $this->conditionProcessor = $this->getConditionProcessor();
        $this->phpConditionDataObject = $this->getPhpConditionDataObject();

        $this->substepService = Core::instantiate(SubstepValidationService::class, $this, $dataObject);
    }

    /**
     * @return FormValidatorExecutor
     */
    public function applyBehaviours()
    {
        /** @var BehavioursManager $behavioursManager */
        $behavioursManager = GeneralUtility::makeInstance(BehavioursManager::class);
        $behavioursManager->applyBehaviourOnFormInstance($this->getFormObject());

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
        foreach ($this->getFormObject()->getDefinition()->getFields() as $field) {
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

        $this->checkFieldStepSupport($field);

        if (false === $this->getResult()->fieldIsOutOfScope($field)
            && true === $field->hasActivation()
            && false === $this->getFieldActivationProcessResult($field)
        ) {
            $this->getResult()->deactivateField($field);
        }

        if (false === $this->getResult()->fieldIsDeactivated($field)) {
            $this->checkFieldValidatorActivation($field);
        }

        $this->markFieldActivationAsChecked($field);
        $this->markFieldActivationCheckEnd($field);
    }

    /**
     * Check if the given field is supported by the current step of the form.
     *
     * @param Field $field
     */
    protected function checkFieldStepSupport(Field $field)
    {
        $validatedStep = $this->dataObject->getValidatedStep();

        if ($validatedStep
            && false === $validatedStep->supportsField($field)
        ) {
            $this->getResult()->markFieldOutOfScope($field);
        }
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
     * @return FormValidatorExecutor
     */
    public function validateFields()
    {
        $validatedStep = $this->dataObject->getValidatedStep();

        if ($validatedStep && $validatedStep->hasSubsteps()) {
            $this->substepService->handleSubsteps();
        } else {
            foreach ($this->getFormObject()->getDefinition()->getFields() as $field) {
                $this->validateField($field);
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

            if (false === $this->getResult()->fieldIsOutOfScope($field)
                && false === $this->getResult()->fieldIsDeactivated($field)
            ) {
                $this->markFieldAsValidated($field);

                // Looping on the field's validators settings...
                foreach ($field->getValidators() as $validator) {
                    if ($this->getResult()->validatorIsDeactivated($validator)) {
                        continue;
                    }

                    $validatorResult = $this->processFieldValidator($field, $validator);
                    $this->getResult()->markFieldAsValidated($field);

                    // Breaking the loop if an error occurred: we stop the validation process for the current field.
                    if ($validatorResult->hasErrors()) {
                        break;
                    }
                }

                $this->callFieldValidationCallback($field);
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
        $form = $this->getFormObject()->getForm();
        $fieldName = $field->getName();
        $fieldValue = ObjectAccess::getProperty($form, $fieldName);
        $validatorDataObject = new ValidatorDataObject($this->getFormObject(), $validator);

        /** @var ValidatorInterface $validatorInstance */
        $validatorInstance = Core::instantiate(
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
     * Loops on registered callbacks that should be called after the given field
     * validation.
     *
     * @param Field $field
     */
    protected function callFieldValidationCallback(Field $field)
    {
        $fieldValidationCallbacks = $this->dataObject->getFieldValidationCallbacks();

        foreach ($fieldValidationCallbacks as $callback) {
            if (is_callable($callback)) {
                call_user_func($callback, $field);
            }
        }
    }

    /**
     * @return FormObject
     */
    public function getFormObject()
    {
        return $this->dataObject->getFormObject();
    }

    /**
     * @return FormResult
     */
    public function getResult()
    {
        return $this->dataObject->getFormResult();
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
        return ConditionProcessorFactory::getInstance()->get($this->getFormObject());
    }

    /**
     * @return PhpConditionDataObject
     */
    protected function getPhpConditionDataObject()
    {
        return new PhpConditionDataObject($this->getFormObject()->getForm(), $this);
    }
}
