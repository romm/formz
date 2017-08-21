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

namespace Romm\Formz\Middleware\Item\Begin;

use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObject\FormObjectFactory;
use Romm\Formz\Middleware\BasicMiddlewareInterface;
use Romm\Formz\Middleware\Processor\MiddlewareProcessor;
use Romm\Formz\Middleware\Signal\After;
use Romm\Formz\Middleware\Signal\SignalObject;

final class BeginMiddleware implements BasicMiddlewareInterface
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
        $this->fetchCurrentStep();
        $this->fetchSubstepsLevel();
    }

    /**
     * This is the first middleware being called, it will send a signal to all
     * middlewares that depend on it.
     */
    public function execute()
    {
        $signalObject = new SignalObject($this->processor, BeginSignal::class, After::class);
        $signalObject->dispatch();
    }

    /**
     * Will check if the current form was submitted by the user. If it is found,
     * the form instance is injected in the form object.
     */
    protected function checkFormSubmission()
    {
        if ($this->processor->inSingleFieldValidationContext()) {
            /*
             * In "single field validation context", there is no need to check
             * for the form submission.
             */
            return;
        }

        $request = $this->processor->getRequest();
        $formObject = $this->processor->getFormObject();
        $formName = $formObject->getName();

        if ($this->requestWasSubmitted()
            && $this->processor->getRequestArguments()->hasArgument($formName)
        ) {
            if (false === $request->hasArgument('formzData')) {
                throw new \Exception('todo'); // @todo
            }

            $form = $this->getFormInstance();

            $formObject->setForm($form);

            $formzData = $request->getArgument('formzData');
            $formObject->getRequestData()->fillFromHash($formzData);

            $proxy = FormObjectFactory::get()->getProxy($form);
            $proxy->markFormAsSubmitted();

            $this->injectFormHashInProxy();
        }
    }

    /**
     * @todo
     */
    protected function fetchCurrentStep()
    {
        $formObject = $this->processor->getFormObject();
        $request = ($formObject->formWasSubmitted())
            ? $this->processor->getRequest()->getReferringRequest()
            : $this->processor->getRequest();

        $formObject->fetchCurrentStep($request);
    }

    /**
     * @todo
     */
    protected function fetchSubstepsLevel()
    {
        $request = $this->processor->getRequest();

        if ($this->requestWasSubmitted()
            && $request->hasArgument('substepsLevel')
        ) {
            $substepLevel = $request->getArgument('substepsLevel');
            FormObjectFactory::get()
                ->getStepService($this->processor->getFormObject())
                ->setSubstepsLevel($substepLevel);
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