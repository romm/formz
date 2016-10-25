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

namespace Romm\Formz\Validation\Validator\Form;

use Romm\Formz\Behaviours\BehavioursManager;
use Romm\Formz\Condition\Processor\PhpProcessor;
use Romm\Formz\Core\Core;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObject;
use Romm\Formz\Utility\FormUtility;
use Romm\Formz\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator as ExtbaseAbstractValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator;

/**
 * This is the abstract form validator, which must be inherited by any custom
 * form validator in order to work properly.
 *
 * Please note that a default form validator already exists if you need a form
 * which does not require any particular action: `DefaultFormValidator`.
 *
 * A form validator should be called to validate any form instance (which is a
 * child of `AbstractForm`). Usually, this is used in controller actions to
 * validate a form sent by the user. Example:
 *
 * /**
 *  * Action called when the Example form is submitted.
 *  *
 *  * @param $exForm
 *  * @validate $exForm Romm.Formz:Form\DefaultFormValidator
 *  * /
 *  public function submitFormAction(ExampleForm $exForm) { ... }
 *
 *******************************************************************************
 *
 * You may use you own custom form validator in order to be able to use the
 * following features:
 *
 * - Fields validation (de)activation:
 *   You are able to handle manually which validation rule for each field of the
 *   form may be activated or not. These functions can be used during the custom
 *   process functions described below.
 *   See the functions:
 *    - `activateField()`
 *    - `deactivateField()`
 *    - `activateFieldValidator()`
 *    - `deactivateFieldValidator()`
 *   And the properties:
 *    - `$deactivatedFields`
 *    - `$deactivatedFieldsValidators`
 *
 * - Pre-validation custom process:
 *   By extending the method `beforeValidationProcess()`, you are able to handle
 *   anything you want just before the form validation begins to loop on every
 *   field. This can be used for instance to (de)activate the validation of
 *   certain fields under very specific circumstances.
 *
 * - In real time custom process:
 *   After each field went trough a validation process, a magic method is called
 *   to allow very low level custom process. The magic method name looks like:
 *   "{lowerCamelCaseFieldName}Validated". For instance, when the "email" field
 *   just went trough the validation process, the method `emailValidated()` is
 *   called.
 *
 * - Post-validation custom process:
 *   After the validation was done on every field of the form, this method is
 *   called to allow you high level process. For instance, let's assume your
 *   form is used to calculate a price estimation depending on information
 *   submitted in the form; when the form went trough the validation process and
 *   got no error, you can run the price estimation, and if any error occurs you
 *   are still able to add an error to `$this->result` (in a controller you do
 *   not have access to it anymore).
 */
abstract class AbstractFormValidator extends GenericObjectValidator
{

    /**
     * @inheritdoc
     */
    protected $supportedOptions = [
        'name' => ['', 'Name of the form.', 'string', true]
    ];

    /**
     * @var FormResult
     */
    protected $result;

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var FormObject
     */
    protected $formObject;

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
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     * @inject
     */
    protected $objectManager;

    /**
     * @var PhpProcessor
     */
    private $phpProcessor;

    /**
     * Array of arbitral data which are handled by validators.
     *
     * @var array
     */
    private $validationData = [];

    /**
     * @var string
     */
    private static $currentValidationName;

    /**
     * @var FormInterface
     */
    private $formClone;

    /**
     * @var array
     */
    private $fieldsValidated = [];

    /**
     * @var array
     */
    private $fieldsActivationChecked = [];

    /**
     * Current queue, used to prevent infinite loop.
     *
     * @var array
     */
    private $fieldsActivationChecking = [];

    /**
     * Contains the validation results of all forms which were validated. The
     * key is the form name (the property `formName` in the form configuration)
     * and the value is an instance of `FormResult`.
     *
     * Note: we need to store the results here, because the TYPO3 request
     * handler builds an instance of Extbase's `Result` from scratch, so we are
     * not able to retrieve the `FormResult` instance afterward.
     *
     * @var FormResult[]
     */
    private static $formsValidationResults = [];

    /**
     * Validates the given Form instance. See class description for more
     * information.
     *
     * @param FormInterface $form The form instance to be validated.
     * @return FormResult
     */
    final public function validate($form)
    {
        $this->form = $form;
        $formClassName = get_class($form);
        $formName = $this->options['name'];
        $this->result = new FormResult();

        $this->formObject = Core::get()->getFormObjectFactory()
            ->getInstanceFromClassName($formClassName, $formName);

        /** @var BehavioursManager $behavioursManager */
        $behavioursManager = GeneralUtility::makeInstance(BehavioursManager::class);
        $behavioursManager->applyBehaviourOnFormInstance($this->form, $this->formObject);

        $this->phpProcessor = GeneralUtility::makeInstance(PhpProcessor::class, $this->formObject, $this->form, $this);

        $this->checkFieldsActivation();
        $this->beforeValidationProcess();

        foreach ($this->formObject->getConfiguration()->getFields() as $fieldName => $field) {
            $this->validateField($fieldName);

            $this->form->setValidationData($this->validationData);

            // A callback after each field validation: `{lowerCamelCaseFieldName}Validated()`
            // Example for field "firstName": `firstNameValidated()`
            $functionName = lcfirst($fieldName . 'Validated');
            if (method_exists($this, $functionName)) {
                $this->$functionName();
            }
        }

        $this->afterValidationProcess();

        if ($this->result->hasErrors()) {
            // Storing the form for possible third party further usage.
            FormUtility::addFormWithErrors($this->form);
        }

        self::$formsValidationResults[$formClassName . '::' . $formName] = $this->result;

        return $this->result;
    }

    /**
     * Returns the validation result of the asked form. The form name matches
     * the property `formName` of the form configuration.
     *
     * @param string $formClassName
     * @param string $formName
     * @return null|FormResult
     */
    public static function getFormValidationResult($formClassName, $formName)
    {
        $key = $formClassName . '::' . $formName;

        return (true === isset(self::$formsValidationResults[$key]))
            ? self::$formsValidationResults[$key]
            : null;
    }

    /**
     * Use this function to (de)activate the validation for some given fields.
     */
    protected function beforeValidationProcess()
    {
    }

    /**
     * Use this function to (de)activate the validation for some given fields.
     *
     * @deprecated use `beforeValidationProcess()` instead
     */
    protected function processForm()
    {
    }

    /**
     * Use this function to run your own processes after the validation ran.
     */
    protected function afterValidationProcess()
    {
    }

    /**
     * Activates the full validation for the given field.
     *
     * @param string $fieldName Name of the field.
     */
    protected function activateField($fieldName)
    {
        if (false !== ($key = array_search($fieldName, $this->deactivatedFields))) {
            unset($this->deactivatedFields[$key]);
        }
    }

    /**
     * Deactivates the full validation for the given field.
     *
     * @param string $fieldName Name of the field.
     */
    protected function deactivateField($fieldName)
    {
        if (false === in_array($fieldName, $this->deactivatedFields)) {
            $this->deactivatedFields[] = $fieldName;
        }
    }

    /**
     * Activates the given validator for the given field.
     *
     * @param    string $fieldName     The name of the field.
     * @param    string $validatorName The name given to the validator.
     */
    protected function activateFieldValidator($fieldName, $validatorName)
    {
        if (isset($this->deactivatedFieldsValidators[$fieldName])) {
            if (false !== ($key = array_search($validatorName, $this->deactivatedFieldsValidators[$fieldName]))) {
                unset($this->deactivatedFieldsValidators[$fieldName][$key]);
            }
        }
    }

    /**
     * Deactivates the given validator for the given field.
     *
     * @param    string $fieldName     The name of the field.
     * @param    string $validatorName The name given to the validator.
     */
    protected function deactivateFieldValidator($fieldName, $validatorName)
    {
        if (false === in_array($fieldName, $this->deactivatedFieldsValidators)) {
            $this->deactivatedFieldsValidators[$fieldName] = [];
        }

        $this->deactivatedFieldsValidators[$fieldName][] = $validatorName;
    }

    /**
     * This function will take care of deactivating the validation for fields
     * that do not match their activation condition.
     */
    private function checkFieldsActivation()
    {
        foreach ($this->formObject->getConfiguration()->getFields() as $fieldName => $field) {
            if (false === in_array($fieldName, $this->fieldsActivationChecked)
                && false === in_array($fieldName, $this->deactivatedFields)
            ) {
                if ($field->hasActivation()
                    && false === isset($this->fieldsActivationChecking[$fieldName])
                ) {
                    $this->fieldsActivationChecking[$fieldName] = true;

                    $activationConditionTree = $this->phpProcessor->getFieldActivationConditionTree($field);
                    if (false === $activationConditionTree) {
                        $this->deactivatedFields[] = $fieldName;
                    }
                }

                foreach ($field->getValidation() as $validationName => $validation) {
                    if ($validation->hasActivation()) {
                        $validationActivationConditionTree = $this->phpProcessor->getFieldValidationActivationConditionTree($field, $validation);
                        if (false === $validationActivationConditionTree) {
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
    }

    /**
     * Will loop on each validation rule and apply it of the field.
     * Errors are stored in `$this->result`.
     *
     * @param string $fieldName The name of the field.
     * @return FormResult
     * @internal
     */
    final public function validateField($fieldName)
    {
        if (false === in_array($fieldName, $this->fieldsValidated)
            && false === in_array($fieldName, $this->deactivatedFields)
            && true === $this->formObject->getConfiguration()->hasField($fieldName)
        ) {
            $this->fieldsValidated[] = $fieldName;
            $field = $this->formObject->getConfiguration()->getField($fieldName);
            $fieldValue = ObjectAccess::getProperty($this->form, $fieldName);

            // Looping on the field's validation settings...
            foreach ($field->getValidation() as $validationName => $validation) {
                if (isset($this->deactivatedFieldsValidators[$fieldName])
                    && in_array($validationName, $this->deactivatedFieldsValidators[$fieldName])
                ) {
                    continue;
                }

                self::$currentValidationName = (string)$validationName;

                $formClone = $this->getFormClone();

                /** @var ExtbaseAbstractValidator $validator */
                $validator = GeneralUtility::makeInstance(
                    $validation->getClassName(),
                    $validation->getOptions(),
                    $formClone,
                    $fieldName,
                    $validation->getMessages()
                );
                $validatorResult = $validator->validate($fieldValue);
                unset($formClone);

                if ($validator instanceof AbstractValidator) {
                    /** @var AbstractValidator $validator */
                    if (!empty($validationData = $validator->getValidationData())) {
                        $this->validationData[$fieldName] = ($this->validationData[$fieldName]) ?: [];
                        $this->validationData[$fieldName] = array_merge(
                            $this->validationData[$fieldName],
                            $validationData
                        );
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
     * @return string
     * @internal
     */
    final public static function getCurrentValidationName()
    {
        return self::$currentValidationName;
    }

    /**
     * Returns a clone of the Form, used to give a "read-only" instance of the
     * form to the validators.
     *
     * @return FormInterface
     * @internal
     */
    private function getFormClone()
    {
        if (!$this->formClone) {
            $this->formClone = clone $this->form;
        }

        return $this->formClone;
    }
}
