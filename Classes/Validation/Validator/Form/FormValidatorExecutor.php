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
use Romm\Formz\Core\Core;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\Definition\Field\Validation\Validator;
use Romm\Formz\Form\Definition\Step\Step\Substep\SubstepDefinition;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Form\FormObject\FormObjectFactory;
use Romm\Formz\Service\MessageService;
use Romm\Formz\Validation\DataObject\ValidatorDataObject;
use Romm\Formz\Validation\Validator\AbstractValidator;
use Romm\Formz\Validation\Validator\Form\DataObject\FormValidatorDataObject;
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
     * @var FormObject
     */
    protected $formObject;

    /**
     * @var FormResult
     */
    protected $result;

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
     * @param FormObject              $formObject
     * @param FormValidatorDataObject $dataObject
     */
    public function __construct(FormObject $formObject, FormValidatorDataObject $dataObject)
    {
        $this->dataObject = $dataObject;
        $this->formObject = $formObject;
        $this->result = $this->dataObject->getFormResult();
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
            if (false === $this->result->fieldIsDeactivated($field)) {
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

        if (false === $this->result->fieldIsOutOfScope($field)
            && true === $field->hasActivation()
            && false === $this->getFieldActivationProcessResult($field)
        ) {
            $this->result->deactivateField($field);
        }

        if (false === $this->result->fieldIsDeactivated($field)) {
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
            $this->result->markFieldOutOfScope($field);
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
                $this->result->deactivateValidator($validator);
            }
        }
    }

    /**
     * @return FormValidatorExecutor
     */
    public function validateFields()
    {
        $validatedStep = $this->dataObject->getValidatedStep();

        if ($validatedStep
            && $validatedStep->hasSubsteps()
        ) {
            $this->handleSubsteps();
        } else {
            foreach ($this->formObject->getDefinition()->getFields() as $field) {
                $this->launchFieldValidation($field);
            }
        }

        return $this;
    }

    /**
     * @todo
     */
    protected function handleSubsteps()
    {
        $stepService = FormObjectFactory::get()->getStepService($this->formObject);

        $firstSubstepDefinition = $this->dataObject->getValidatedStep()->getSubsteps()->getFirstSubstepDefinition();
        $substepDefinition = $firstSubstepDefinition;
        $currentSubstepDefinition = null;

        $substepsLevel = $stepService->getSubstepsLevel();
        $stepService->setSubstepsLevel(1);
        $substepsLevelCounter = 0;

        while ($substepDefinition && $substepsLevel > 0) {
            $substepsLevel--;
            $substepsLevelCounter++;
            $phpResult = true;

            if ($substepDefinition->hasActivation()) {
                $phpResult = $this->getSubstepDefinitionActivationResult($substepDefinition);
            }

            if (true === $phpResult) {
                $supportedFields = $substepDefinition->getSubstep()->getSupportedFields();

                foreach ($supportedFields as $supportedField) {
                    $this->launchFieldValidation($supportedField->getField());
                }
            }

            if ($substepsLevel === 0
                || $this->result->hasErrors()
            ) {
                $currentSubstepDefinition = $substepDefinition;
                $stepService->setSubstepsLevel($substepsLevelCounter);
                break;
            }

            $substepDefinition = $substepDefinition->hasNextSubstep()
                ? $substepDefinition->getNextSubstep()
                : null;
        }

        if (null !== $currentSubstepDefinition
            && $this->dataObject->getValidatedStep() === $stepService->getCurrentStep()
        ) {
            if ($this->result->hasErrors()) {
                $stepService->setCurrentSubstepDefinition($currentSubstepDefinition);
            } else {
                list($nextSubstep, $substepsLevelIncrease) = $this->getNextSubstep($currentSubstepDefinition);

                if ($nextSubstep) {
                    $stepService->setCurrentSubstepDefinition($nextSubstep);
                    $stepService->setSubstepsLevel($stepService->getSubstepsLevel() + $substepsLevelIncrease);
                } else {
                    $stepService->markLastSubstepAsValidated();
                }
            }
        }
    }

    protected function getNextSubstep(SubstepDefinition $substepDefinition)
    {
        $substepsLevelIncrease = 0;
        $nextSubstep = null;

        while ($substepDefinition) {
            if (false === $substepDefinition->hasNextSubstep()) {
                break;
            } else {
                $substepDefinition = $substepDefinition->getNextSubstep();
                $substepsLevelIncrease++;

                if (false === $substepDefinition->hasActivation()) {
                    $nextSubstep = $substepDefinition;
                    break;
                } else {
                    $phpResult = $this->getSubstepDefinitionActivationResult($substepDefinition);

                    if (true === $phpResult) {
                        $nextSubstep = $substepDefinition;
                        break;
                    }
                }
            }
        }

        return [$nextSubstep, $substepsLevelIncrease];
    }

    /**
     * @param SubstepDefinition $substepDefinition
     * @return bool
     */
    protected function getSubstepDefinitionActivationResult(SubstepDefinition $substepDefinition)
    {
        $conditionProcessor = ConditionProcessorFactory::getInstance()->get($this->formObject);
        $tree = $conditionProcessor->getActivationConditionTreeForSubstep($substepDefinition);
        $dataObject = new PhpConditionDataObject($this->formObject->getForm(), $this);

        return $tree->getPhpResult($dataObject);
    }

    /**
     * @todo
     *
     * @param Field $field
     */
    protected function launchFieldValidation(Field $field)
    {
        if (false === $this->fieldWasValidated($field)) {
            $this->validateField($field);

            if ($this->fieldWasValidated($field)) {
                $this->callFieldValidationCallback($field);
            }
        }
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

            if (false === $this->result->fieldIsOutOfScope($field)
                && false === $this->result->fieldIsDeactivated($field)
            ) {
                $this->markFieldAsValidated($field);

                // Looping on the field's validators settings...
                foreach ($field->getValidators() as $validator) {
                    if ($this->result->validatorIsDeactivated($validator)) {
                        continue;
                    }

                    $validatorResult = $this->processFieldValidator($field, $validator);
                    $this->result->markFieldAsValidated($field);

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

        $this->result->forProperty($fieldName)->merge($validatorResult);
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
     * @return FormResult
     */
    public function getResult()
    {
        return $this->result;
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
