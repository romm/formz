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

use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Core\Core;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Exceptions\InvalidArgumentTypeException;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Service\FormService;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator as ExtbaseAbstractValidator;

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
abstract class AbstractFormValidator extends ExtbaseAbstractValidator implements FormValidatorInterface
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
     * @var FormValidatorExecutor
     */
    private $formValidatorExecutor;

    /**
     * Initializes all class variables.
     *
     * @param FormInterface $form
     * @throws InvalidArgumentTypeException
     */
    private function initializeValidator($form)
    {
        if (false === $form instanceof FormInterface) {
            throw InvalidArgumentTypeException::validatingWrongFormType(get_class($form));
        }

        $this->form = $form;
        $this->result = new FormResult;
        $this->formValidatorExecutor = $this->getFormValidatorExecutor($form);
    }

    /**
     * Checks the given form instance, and launches the validation if it is a
     * correct form.
     *
     * @param FormInterface $form The form instance to be validated.
     * @return FormResult
     */
    final public function validate($form)
    {
        $this->initializeValidator($form);

        $formObject = $this->formValidatorExecutor->getFormObject();
        $formObject->markFormAsSubmitted();
        $formObject->setForm($form);

        $this->validateGhost($form, false);

        $formObject->setFormResult($this->result);

        return $this->result;
    }

    /**
     * Validates the form, but wont save form data in the form object.
     *
     * @param FormInterface $form
     * @param bool          $initialize
     * @return FormResult
     * @internal
     */
    public function validateGhost($form, $initialize = true)
    {
        if ($initialize) {
            $this->initializeValidator($form);
        }

        $this->isValid($form);

        return $this->result;
    }

    /**
     * Runs the whole validation workflow.
     *
     * @param FormInterface $form
     */
    final public function isValid($form)
    {
        $this->formValidatorExecutor->applyBehaviours();
        $this->formValidatorExecutor->checkFieldsActivation();

        $this->beforeValidationProcess();

        $this->formValidatorExecutor->validateFields(function (Field $field) {
            $this->callAfterFieldValidationMethod($field);
        });

        $this->afterValidationProcess();

        if ($this->result->hasErrors()) {
            // Storing the form for possible third party further usage.
            FormService::addFormWithErrors($form);
        }
    }

    /**
     * Override this function in your child class to handle some pre-validation
     * process.
     */
    protected function beforeValidationProcess()
    {
    }

    /**
     * Override this function in your child class to handle some post-validation
     * process.
     */
    protected function afterValidationProcess()
    {
    }

    /**
     * After each field has been validated, a matching method can be called if
     * it exists in the child class.
     *
     * The syntax is `{lowerCamelCaseFieldName}Validated()`.
     *
     * Example: for field `firstName` - `firstNameValidated()`.
     *
     * @param Field $field
     */
    private function callAfterFieldValidationMethod(Field $field)
    {
        $functionName = lcfirst($field->getFieldName() . 'Validated');

        if (method_exists($this, $functionName)) {
            call_user_func([$this, $functionName]);
        }
    }

    /**
     * @param FormInterface $form
     * @return FormValidatorExecutor
     */
    protected function getFormValidatorExecutor(FormInterface $form)
    {
        /** @var FormValidatorExecutor $formValidatorExecutor */
        $formValidatorExecutor = Core::instantiate(FormValidatorExecutor::class, $form, $this->options['name'], $this->result);

        return $formValidatorExecutor;
    }
}
