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

namespace Romm\Formz\Domain\Middleware\FormValidation;

use Romm\Formz\Core\Core;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Domain\Middleware\FieldValidation\FieldValidationArguments;
use Romm\Formz\Domain\Middleware\FieldValidation\FieldValidationSignal;
use Romm\Formz\Middleware\Application\OnBeginMiddleware;
use Romm\Formz\Middleware\PresetMiddlewareInterface;
use Romm\Formz\Middleware\Scope\ReadScope;
use Romm\Formz\Middleware\Signal\SendsSignal;
use Romm\Formz\Validation\Form\AbstractFormValidator;

/**
 * This middleware takes care of validating the form instance, with a proper
 * form validator instance.
 *
 * You can bind middlewares to the signal `FormValidationSignal`, which will be
 * dispatched if and only if the form was submitted by the user.
 */
class FormValidationMiddleware extends OnBeginMiddleware implements PresetMiddlewareInterface, SendsSignal
{
    /**
     * @var \Romm\Formz\Domain\Middleware\FormValidation\FormValidationMiddlewareOption
     */
    protected $options;

    /**
     * @var AbstractFormValidator
     */
    protected $validator;

    /**
     * @var array
     */
    protected static $defaultScopesBlackList = [ReadScope::class];

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
     * @return array
     */
    public function getAllowedSignals()
    {
        return [FormValidationSignal::class, FieldValidationSignal::class];
    }
}
