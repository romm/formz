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
use Romm\Formz\Core\Core;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObjectFactory;
use Romm\Formz\Validation\DataObject\ValidatorDataObject;
use Romm\Formz\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
     * Contains the fields which will not be checked.
     * You can override this property in your children class to set the fields
     * which will not be checked by default.
     *
     * @var array
     */
    protected $deactivatedFields = [];

    /**
     * Contains the validators which will not be checked for certain fields.
     * You can override this property in your children class to set the
     * validators of fields which will not be checked by default.
     *
     * Example:
     * protected $deactivatedFieldsValidators = [
     *      'name'  => ['required'],
     *      'email' => ['mustBeEmail', 'required']
     * ];
     *
     * @var array
     */
    protected $deactivatedFieldsValidators = [];

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
        foreach ($this->formObject->getConfiguration()->getFields() as $fieldName => $field) {
            if (false === in_array($fieldName, $this->fieldsActivationChecked)
                && false === in_array($fieldName, $this->deactivatedFields)
            ) {
                $phpConditionDataObject = new PhpConditionDataObject($this->form, $this);

                if ($field->hasActivation()
                    && false === isset($this->fieldsActivationChecking[$fieldName])
                ) {
                    $this->fieldsActivationChecking[$fieldName] = true;

                    $activation = $this->conditionProcessor
                        ->getActivationConditionTreeForField($field)
                        ->getPhpResult($phpConditionDataObject);

                    if (false === $activation) {
                        $this->deactivatedFields[] = $fieldName;
                    }
                }

                foreach ($field->getValidation() as $validationName => $validation) {
                    if ($validation->hasActivation()) {
                        $activation = $this->conditionProcessor
                            ->getActivationConditionTreeForValidation($validation)
                            ->getPhpResult($phpConditionDataObject);

                        if (false === $activation) {
                            if (false === isset($this->deactivatedFieldsValidators[$fieldName])) {
                                $this->deactivatedFieldsValidators[$fieldName] = [];
                            }

                            $this->deactivatedFieldsValidators[$fieldName][] = $validationName;
                        }
                    }
                }

                unset($this->fieldsActivationChecking[$fieldName]);
                $this->fieldsActivationChecked[] = $fieldName;
            }

            if (true === in_array($fieldName, $this->deactivatedFields)
                && null === $this->result->fieldIsDeactivated($fieldName)
            ) {
                $this->result->deactivateField($fieldName);
            }
        }

        return $this;
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
            && false === in_array($fieldName, $this->deactivatedFields)
        ) {
            $this->fieldsValidated[] = $fieldName;
            $fieldValue = ObjectAccess::getProperty($this->form, $fieldName);
            $formClone = clone $this->form;

            // Looping on the field's validation settings...
            foreach ($field->getValidation() as $validationName => $validation) {
                if (isset($this->deactivatedFieldsValidators[$fieldName])
                    && in_array($validationName, $this->deactivatedFieldsValidators[$fieldName])
                ) {
                    continue;
                }

                $validatorDataObject = new ValidatorDataObject($this->formObject, $formClone, $validation);

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

                // Breaking the loop if an error occurred: we stop the validation process for the current field.
                if ($validatorResult->hasErrors()) {
                    break;
                }
            }
        }

        return $this->result;
    }

    /**
     * @return FormResult
     */
    public function getResult()
    {
        return $this->result;
    }
}
