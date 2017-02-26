<?php
/*
 * 2017 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Formz project.
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
use Romm\Formz\Form\FormObjectFactory;
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
     * @param FormInterface $form
     * @param string        $name
     * @param FormResult    $result
     */
    public function __construct(FormInterface $form, $name, FormResult $result)
    {
        /** @var FormObjectFactory $formObjectFactory */
        $formObjectFactory = Core::instantiate(FormObjectFactory::class);

        $this->form = $form;
        $this->result = $result;
        $this->formObject = $formObjectFactory->getInstanceFromFormInstance($form, $name);
        $this->conditionProcessor = ConditionProcessorFactory::getInstance()->get($this->formObject);
    }

    /**
     * @return FormValidatorExecutor
     */
    public function applyBehaviours()
    {
        /** @var BehavioursManager $behavioursManager */
        $behavioursManager = GeneralUtility::makeInstance(BehavioursManager::class);
        $behavioursManager->applyBehaviourOnFormInstance($this->form, $this->formObject);

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
        foreach ($this->formObject->getConfiguration()->getFields() as $field) {
            $fieldName = $field->getFieldName();

            if (false === in_array($fieldName, $this->fieldsActivationChecked)
                && false === $this->result->fieldIsDeactivated($field)
            ) {
                $this->checkFieldActivation($field);

                $this->fieldsActivationChecked[] = $fieldName;
            }
        }

        return $this;
    }

    /**
     * @param Field $field
     */
    protected function checkFieldActivation(Field $field)
    {
        $fieldName = $field->getFieldName();

        if (isset($this->fieldsActivationChecking[$fieldName])) {
            return;
        }

        $this->fieldsActivationChecking[$fieldName] = true;

        if ($field->hasActivation()) {
            $activation = $this->conditionProcessor
                ->getActivationConditionTreeForField($field)
                ->getPhpResult($this->getPhpConditionDataObject());

            if (false === $activation) {
                $this->result->deactivateField($field);
            }
        }

        $this->checkFieldValidationActivation($field);
        unset($this->fieldsActivationChecking[$fieldName]);
    }

    /**
     * @param Field $field
     */
    protected function checkFieldValidationActivation(Field $field)
    {
        foreach ($field->getValidation() as $validation) {
            if ($validation->hasActivation()) {
                $activation = $this->conditionProcessor
                    ->getActivationConditionTreeForValidation($validation)
                    ->getPhpResult($this->getPhpConditionDataObject());

                if (false === $activation) {
                    $this->result->deactivateValidation($validation);
                }
            }
        }
    }

    /**
     * @param callable $callback
     * @return FormValidatorExecutor
     */
    public function validateFields(callable $callback)
    {
        foreach ($this->formObject->getConfiguration()->getFields() as $field) {
            $this->validateField($field);

            $callback($field);
        }

        return $this;
    }

    /**
     * Will loop on each validation rule and apply it of the field.
     * Errors are stored in `$this->result`.
     *
     * @param Field $field
     * @return FormResult
     * @internal
     */
    public function validateField(Field $field)
    {
        $fieldName = $field->getFieldName();

        if (false === in_array($fieldName, $this->fieldsValidated)
            && false === $this->result->fieldIsDeactivated($field)
        ) {
            $this->fieldsValidated[] = $fieldName;

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

        return $this->result;
    }

    /**
     * @param Field      $field
     * @param Validation $validation
     * @return Result
     */
    protected function processFieldValidation(Field $field, Validation $validation)
    {
        $fieldName = $field->getFieldName();
        $fieldValue = ObjectAccess::getProperty($this->form, $fieldName);
        $validatorDataObject = new ValidatorDataObject($this->formObject, $this->form, $validation);

        /** @var ValidatorInterface $validator */
        $validator = GeneralUtility::makeInstance(
            $validation->getClassName(),
            $validation->getOptions(),
            $validatorDataObject
        );

        $validatorResult = $validator->validate($fieldValue);

        if ($validator instanceof AbstractValidator) {
            if (!empty($validationData = $validator->getValidationData())) {
                $this->validationData[$fieldName] = ($this->validationData[$fieldName]) ?: [];
                $this->validationData[$fieldName] = array_merge(
                    $this->validationData[$fieldName],
                    $validationData
                );

                $this->form->setValidationData($this->validationData);
            }
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
