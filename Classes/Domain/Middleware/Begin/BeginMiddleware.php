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

namespace Romm\Formz\Domain\Middleware\Begin;

use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObject\FormObjectFactory;
use Romm\Formz\Middleware\Processor\MiddlewareProcessor;
use Romm\Formz\Middleware\Signal\After;
use Romm\Formz\Middleware\Signal\Element\SignalObject;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class BeginMiddleware
{
    /**
     * @var MiddlewareProcessor
     */
    private $processor;

    /**
     * Initialization of this middleware.
     */
    public function initialize()
    {
        $this->checkFormSubmission();
    }

    /**
     * This is the first middleware being called, it will send a signal to all
     * middlewares that depend on it.
     */
    public function execute()
    {
        /** @var SignalObject $signalObject */
        $signalObject = GeneralUtility::makeInstance(SignalObject::class, $this->processor, BeginSignal::class, After::class);
        $signalObject->dispatch();
    }

    /**
     * Will check if the current form was submitted by the user. If it is found,
     * the form instance is injected in the form object.
     */
    protected function checkFormSubmission()
    {
        $formObject = $this->processor->getFormObject();

        if ($formObject->hasForm()) {
            return;
        }

        $request = $this->processor->getRequest();
        $formName = $formObject->getName();

        if ($this->requestWasSubmitted()
            && null === $request->getOriginalRequest()
            && $this->processor->getRequestArguments()->hasArgument($formName)
        ) {
            if (false === $request->hasArgument('formData')) {
                throw new \Exception('todo'); // @todo
            }

            $form = $this->getFormInstance();

            $formObject->setForm($form);

            $formData = $request->getArgument('formData');
            $formObject->getRequestData()->fillFromHash($formData);

            $proxy = FormObjectFactory::get()->getProxy($form);
            $proxy->markFormAsSubmitted();

            $this->injectFormHashInProxy();
        }
    }

    /**
     * Fetches the form hash from the request data that has been submitted with
     * the form, and injects it in the form proxy.
     */
    protected function injectFormHashInProxy()
    {
        $formObject = $this->processor->getFormObject();
        $hash = $formObject->getRequestData()->getFormHash();

        $proxy = FormObjectFactory::get()->getProxy($formObject->getForm());
        $proxy->setFormHash($hash);
    }

    /**
     * @return FormInterface
     */
    protected function getFormInstance()
    {
        $formName = $this->processor->getFormObject()->getName();
        $formArray = $this->processor->getRequest()->getArgument($formName);
        $argument = $this->processor->getRequestArguments()->getArgument($formName);

        return $argument->setValue($formArray)->getValue();
    }

    /**
     * @param MiddlewareProcessor $middlewareProcessor
     */
    final public function bindMiddlewareProcessor(MiddlewareProcessor $middlewareProcessor)
    {
        $this->processor = $middlewareProcessor;
    }

    /**
     * @return bool
     */
    protected function requestWasSubmitted()
    {
        return $this->processor->getRequest()->getMethod() === 'POST';
    }

}
