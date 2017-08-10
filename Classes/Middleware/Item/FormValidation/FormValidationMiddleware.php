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

namespace Romm\Formz\Middleware\Item\FormValidation;

use Romm\Formz\Core\Core;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Middleware\Item\FieldValidation\FieldValidationArguments;
use Romm\Formz\Middleware\Item\FieldValidation\FieldValidationSignal;
use Romm\Formz\Middleware\Item\OnBeginMiddleware;
use Romm\Formz\Middleware\Processor\PresetMiddlewareInterface;
use Romm\Formz\Middleware\Processor\RemoveFromSingleFieldValidationContext;
use Romm\Formz\Middleware\Signal\SendsMiddlewareSignal;
use Romm\Formz\Validation\Validator\Form\AbstractFormValidator;

/**
 * This middleware takes care of validating the form instance, with a proper
 * form validator instance.
 *
 * You can bind middlewares to the signal `FormValidationSignal`, which will be
 * dispatched if and only if the form was submitted by the user.
 *
 * Please note that this middleware will not be called when being in a "single
 * field validation context".
 *
 * @see \Romm\Formz\Middleware\Processor\RemoveFromSingleFieldValidationContext
 */
class FormValidationMiddleware extends OnBeginMiddleware implements PresetMiddlewareInterface, SendsMiddlewareSignal, RemoveFromSingleFieldValidationContext
{
    /**
     * @var \Romm\Formz\Middleware\Item\FormValidation\FormValidationMiddlewareOption
     */
    protected $options;

    /**
     * @var AbstractFormValidator
     */
    protected $validator;

    /**
     * @see FormValidationMiddleware
     */
    protected function process()
    {
        $formObject = $this->getFormObject();

        $this->beforeSignal(FormValidationSignal::class)->dispatch();

        if ($formObject->hasForm()
            && $formObject->formWasSubmitted()
        ) {
            $this->validator = Core::instantiate(
                $this->options->getFormValidatorClassName(),
                ['form' => $formObject->getForm()]
            );

            $this->injectFieldValidationCallback();
            $this->injectCurrentStep();

            $this->validator->validate($formObject->getForm());

            $this->afterSignal(FormValidationSignal::class)->dispatch();
        }
    }

    /**
     * Injects a callback in the form validator data object. The callback will
     * be called each time a field is validated by the validator, and will allow
     * to dispatch a field validation signal to other middlewares.
     */
    protected function injectFieldValidationCallback()
    {
        $callback = function (Field $field) {
            $this->afterSignal(FieldValidationSignal::class)
                ->withArguments(new FieldValidationArguments($field))
                ->dispatch();
        };

        $this->validator->getDataObject()->addFieldValidationCallback($callback);
    }

    /**
     * Injects the current step in the form validator.
     */
    protected function injectCurrentStep()
    {
        $currentStep = $this->getCurrentStep();

        if ($currentStep) {
            $this->validator->getDataObject()->setValidatedStep($currentStep);
        }
    }

    /**
     * @return array
     */
    public function getAllowedSignals()
    {
        return [FormValidationSignal::class, FieldValidationSignal::class];
    }
}
