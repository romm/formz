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

namespace Romm\Formz\Middleware\Item\FormInjection;

use Romm\Formz\Core\Core;
use Romm\Formz\Middleware\Item\OnBeginMiddleware;
use Romm\Formz\Middleware\Processor\PresetMiddlewareInterface;
use Romm\Formz\Middleware\Signal\SendsMiddlewareSignal;

/**
 * This middleware takes care of creating new form instances, based on the
 * arguments of the current request.
 *
 * It is based on the controller action of the current context: each argument
 * that has a form type (class that implements `FormInterface`) and is not found
 * in the request arguments is injected with an empty form instance.
 *
 * The goal is to provide a form instance to the controller in every case, so
 * the developer can manipulate it easily, for instance by pre-setting values.
 */
class FormInjectionMiddleware extends OnBeginMiddleware implements PresetMiddlewareInterface, SendsMiddlewareSignal
{
    /**
     * @var int
     */
    protected $priority = self::PRIORITY_INJECT_FORM;

    /**
     * @see FormInjectionMiddleware
     */
    protected function process()
    {
        $formObject = $this->getFormObject();

        $this->beforeSignal()->dispatch();

        if (false === $formObject->hasForm()) {
            /*
             * Creating an empty instance of the form.
             */
            $formClassName = $formObject->getClassName();
            $form = Core::get()->getObjectManager()->getEmptyObject($formClassName);

            /*
             * The form instance is injected in the form object, for further
             * usage.
             */
            $formObject->setForm($form);
        }

        $this->afterSignal()->dispatch();
    }

    /**
     * @return array
     */
    public function getAllowedSignals()
    {
        return [FormInjectionSignal::class];
    }
}
