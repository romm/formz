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

namespace Romm\Formz\Middleware\Item\End;

use Romm\Formz\Middleware\BasicMiddlewareInterface;
use Romm\Formz\Middleware\Processor\MiddlewareProcessor;
use Romm\Formz\Middleware\Signal\Before;
use Romm\Formz\Middleware\Signal\SignalObject;

final class EndMiddleware implements BasicMiddlewareInterface
{
    /**
     * @var MiddlewareProcessor
     */
    private $processor;

    /**
     * This is the last middleware being called, it will send a signal to all
     * middlewares that depend on it.
     */
    public function execute()
    {
        $signalObject = new SignalObject($this->processor, EndSignal::class, Before::class);
        $signalObject->dispatch();

        $this->injectFormInRequest();
        $this->injectFormResultInRequest();
    }

    /**
     * The form instance is injected in the request argument.
     */
    protected function injectFormInRequest()
    {
        $formObject = $this->processor->getFormObject();

        if ($formObject->hasForm()) {
            $this->processor->getRequest()->setArgument(
                $formObject->getName(),
                $formObject->getForm()
            );
        }
    }

    /**
     * Will inject the form validation result that was manipulated by other
     * middlewares in the current request.
     */
    protected function injectFormResultInRequest()
    {
        if ($this->processor->inSingleFieldValidationContext()) {
            /*
             * In "single field validation context", there is no need to inject
             * the form result in the request.
             */
            return;
        }

        $request = $this->processor->getRequest();
        $result = $this->processor->getFormObject()->getFormResult();
        $formName = $this->processor->getFormObject()->getName();

        $requestResult = $request->getOriginalRequestMappingResults();
        $requestResult->forProperty($formName)->merge($result);
        $request->setOriginalRequestMappingResults($requestResult);
    }

    /**
     * @param MiddlewareProcessor $middlewareProcessor
     */
    final public function bindMiddlewareProcessor(MiddlewareProcessor $middlewareProcessor)
    {
        $this->processor = $middlewareProcessor;
    }
}
