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

use Romm\Formz\Core\Core;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Exceptions\InvalidArgumentTypeException;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Form\FormObject\FormObjectFactory;
use Romm\Formz\Form\FormObject\FormObjectProxy;
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
        'name'  => ['', 'Name of the form.', 'string'],
        'dummy' => [false, 'Dummy mode?', 'bool']
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
     * @var FormValidatorExecutor
     */
    private $formValidatorExecutor;

    /**
     * Initializes all class variables.
     *
     * @param FormInterface $form
     * @throws InvalidArgumentTypeException
     */
    protected function initializeValidator($form)
    {
        if (false === $form instanceof FormInterface) {
            throw InvalidArgumentTypeException::validatingWrongFormType(get_class($form));
        }

        $this->form = $form;
        $this->formObject = $this->getFormObject();
        $this->formValidatorExecutor = $this->getFormValidatorExecutor();
        $this->result = $this->formObject->getFormResult();
    }

    /**
     * Checks the given form instance, and launches the validation if it is a
     * correct form.
     *
     * @param FormInterface $form The form instance to be validated.
     * @return FormResult
     */
    public function validate($form)
    {
        $this->initializeValidator($form);

        if (true !== $this->options['dummy']) {
            $proxy = $this->getProxy($form);
            $proxy->markFormAsValidated();
            $proxy->markFormAsSubmitted();
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
        $functionName = lcfirst($field->getName() . 'Validated');

        if (method_exists($this, $functionName)) {
            call_user_func([$this, $functionName]);
        }
    }

    /**
     * @return FormValidatorExecutor
     */
    protected function getFormValidatorExecutor()
    {
        /** @var FormValidatorExecutor $formValidatorExecutor */
        $formValidatorExecutor = Core::instantiate(FormValidatorExecutor::class, $this->formObject);

        return $formValidatorExecutor;
    }

    /**
     * @return FormObject
     */
    protected function getFormObject()
    {
        return FormObjectFactory::get()->registerAndGetFormInstance($this->form, $this->options['name']);
    }

    /**
     * @param FormInterface $form
     * @return FormObjectProxy
     */
    protected function getProxy(FormInterface $form)
    {
        return FormObjectFactory::get()->getProxy($form);
    }
}
