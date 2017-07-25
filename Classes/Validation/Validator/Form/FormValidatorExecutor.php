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
use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Configuration\Form\Field\Validation\Validation;
use Romm\Formz\Core\Core;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObject;
use Romm\Formz\Form\FormObjectFactory;
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
     * @var FormInterface
     */
    protected $form;

    /**
     * @var string
     */
    protected $formName;

    /**
     * @var FormResult
     */
    protected $result;

    /**
     * @var FormObject
     */
    private $formObject;

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
     * @param FormInterface $form
     * @param string        $formName
     * @param FormResult    $result
     */
    public function __construct(FormInterface $form, $formName, FormResult $result)
    {
        $this->form = $form;
        $this->formName = $formName;
        $this->result = $result;
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
        foreach ($this->getFormObject()->getConfiguration()->getFields() as $field) {
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

        if (true === $field->hasActivation()
            && false === $this->getFieldActivationProcessResult($field)
        ) {
            $this->result->deactivateField($field);
        }

        $this->checkFieldValidationActivation($field);

        $this->markFieldActivationAsChecked($field);
        $this->markFieldActivationCheckEnd($field);
    }

    /**
     * @param Field $field
     */
    protected function checkFieldValidationActivation(Field $field)
    {
        foreach ($field->getValidation() as $validation) {
            if (true === $validation->hasActivation()
                && false === $this->getValidationActivationProcessResult($validation)
            ) {
                $this->result->deactivateValidation($validation);
            }
        }
    }

    /**
     * @param callable $callback
     * @return FormValidatorExecutor
     */
    public function validateFields(callable $callback = null)
    {
        foreach ($this->getFormObject()->getConfiguration()->getFields() as $field) {
            $this->validateField($field);

            if ($this->fieldWasValidated($field)
                && $callback
            ) {
                call_user_func($callback, $field);
            }
        }

        return $this;
    }

    /**
     * Will loop on each validation rule and apply it of the field.
     * Errors are stored in `$this->result`.
     *
     * @param Field $field
     */
    public function validateField(Field $field)
    {
        if (false === $this->fieldWasValidated($field)) {
            $this->checkFieldActivation($field);

            if (false === $this->result->fieldIsDeactivated($field)) {
                $this->markFieldAsValidated($field);

                // Looping on the field's validation settings...
                foreach ($field->getValidation() as $validation) {
                    if ($this->result->validationIsDeactivated($validation)) {
                        continue;
                    }

                    $validatorResult = $this->processFieldValidation($field, $validation);

                    // Breaking the loop if an error occurred: we stop the validation process for the current field.
                    if ($validatorResult->hasErrors()) {
                        break;
                    }
                }
            }
        }
    }

    /**
     * @param Field      $field
     * @param Validation $validation
     * @return Result
     */
    protected function processFieldValidation(Field $field, Validation $validation)
    {
        $fieldName = $field->getName();
        $fieldValue = ObjectAccess::getProperty($this->form, $fieldName);
        $validatorDataObject = new ValidatorDataObject($this->getFormObject(), $validation);

        /** @var ValidatorInterface $validator */
        $validator = Core::instantiate(
            $validation->getClassName(),
            $validation->getOptions(),
            $validatorDataObject
        );

        $validatorResult = $validator->validate($fieldValue);
        $validatorResult = MessageService::get()->sanitizeValidatorResult($validatorResult, $validation->getName());

        if ($validator instanceof AbstractValidator
            && false === empty($validationData = $validator->getValidationData())
        ) {
            $this->validationData[$fieldName] = ($this->validationData[$fieldName]) ?: [];
            $this->validationData[$fieldName] = array_merge(
                $this->validationData[$fieldName],
                $validationData
            );

            $this->form->setValidationData($this->validationData);
        }

        $this->result->forProperty($fieldName)->merge($validatorResult);
        unset($validatorDataObject);

        return $validatorResult;
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
        return $this->getConditionProcessor()
            ->getActivationConditionTreeForField($field)
            ->getPhpResult($this->getPhpConditionDataObject());
    }

    /**
     * @param Validation $validation
     * @return bool
     */
    protected function getValidationActivationProcessResult(Validation $validation)
    {
        return $this->getConditionProcessor()
            ->getActivationConditionTreeForValidation($validation)
            ->getPhpResult($this->getPhpConditionDataObject());
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
     * @return FormObject
     */
    public function getFormObject()
    {
        if (null === $this->formObject) {
            /** @var FormObjectFactory $formObjectFactory */
            $formObjectFactory = Core::instantiate(FormObjectFactory::class);

            $this->formObject = $formObjectFactory->getInstanceFromFormInstance($this->form, $this->formName);
        }

        return $this->formObject;
    }

    /**
     * @return ConditionProcessor
     */
    protected function getConditionProcessor()
    {
        if (null === $this->conditionProcessor) {
            $this->conditionProcessor = ConditionProcessorFactory::getInstance()->get($this->getFormObject());
        }

        return $this->conditionProcessor;
    }

    /**
     * @return PhpConditionDataObject
     */
    protected function getPhpConditionDataObject()
    {
        if (null === $this->phpConditionDataObject) {
            $this->phpConditionDataObject = new PhpConditionDataObject($this->form, $this);
        }

        return $this->phpConditionDataObject;
    }
}
