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

namespace Romm\Formz\Controller;

use Exception;
use Romm\Formz\Controller\Processor\ControllerProcessor;
use Romm\Formz\Core\Core;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Middleware\Processor\MiddlewareProcessor;
use Romm\Formz\Middleware\Request\Exception\ForwardException;
use Romm\Formz\Middleware\Request\Exception\RedirectException;
use Romm\Formz\Middleware\Request\Exception\StopPropagationException;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;

/**
 * This is the main form controller, it will be called between a request
 * dispatching and a controller action.
 *
 * It will process the current action being called, and analyze every parameter
 * of the method that is a form instance. For each form, all its registered
 * middlewares will be called, allowing manipulation of the request, the form
 * validation, and more.
 */
class FormController extends ActionController
{
    /**
     * @var ControllerProcessor
     */
    protected $processor;

    /**
     * Main action used to dispatch the request properly, depending on FormZ
     * configuration.
     *
     * The request is based on the previously called controller action, and is
     * used to list which forms are handled (every argument of the action method
     * that implements the interface `FormInterface`).
     *
     * Middlewares will be called for each form argument, and may modify the
     * request, which is then dispatched again with modified data.
     *
     * @throws Exception
     */
    public function processFormAction()
    {
        try {
            $this->invokeMiddlewares();
            $this->manageRequestResult();
        } catch (Exception $exception) {
            if ($exception instanceof StopPropagationException) {
                if ($exception instanceof RedirectException) {
                    $this->redirectFromException($exception);
                } elseif (false === $exception instanceof ForwardException) {
                    $this->forwardToReferrer();
                }
            } else {
                throw $exception;
            }
        }

        $this->continueRequest();
    }

    /**
     * @param FormObject $formObject
     */
    public function formObjectErrorAction(FormObject $formObject)
    {
        $this->view->assign('formObject', $formObject);
    }

    /**
     * Will fetch every form argument for this request, and dispatch every
     * middleware registered in its definition.
     */
    protected function invokeMiddlewares()
    {
        foreach ($this->processor->getRequestForms() as $formObject) {
            /** @var MiddlewareProcessor $middlewareProcessor */
            $middlewareProcessor = Core::instantiate(MiddlewareProcessor::class, $formObject, $this->processor);

            $middlewareProcessor->run();
        }
    }

    /**
     * Will check if the request result contains error; if errors are found, the
     * request is forwarded to the referring request, with the arguments of the
     * current request.
     */
    protected function manageRequestResult()
    {
        $result = $this->processor->getRequest()->getOriginalRequestMappingResults();
        $this->request->setOriginalRequestMappingResults($result);

        if ($result->hasErrors()) {
            $this->forwardToReferrer();
        }
    }

    /**
     * Forwards the request to the original request that led to this controller.
     *
     * @throws StopActionException
     */
    protected function continueRequest()
    {
        $this->request->setDispatched(false);
        $originalRequest = $this->processor->getRequest();

        $this->request->setPluginName($originalRequest->getPluginName());
        $this->request->setControllerVendorName($originalRequest->getControllerVendorName());
        $this->request->setControllerExtensionName($originalRequest->getControllerExtensionName());
        $this->request->setControllerName($originalRequest->getControllerName());
        $this->request->setControllerActionName($originalRequest->getControllerActionName());
        $this->request->setArguments($this->processor->getRequest()->getArguments());

        throw new StopActionException;
    }

    /**
     * Forwards to the referrer request. It will also fill the arguments of the
     * action with the ones from the source request.
     *
     * @throws StopActionException
     */
    protected function forwardToReferrer()
    {
        /*
         * If the original request is filled, a forward to referrer has already
         * been done.
         */
        if ($this->request->getOriginalRequest()) {
            return;
        }

        $referringRequest = $this->processor->getRequest()->getReferringRequest();

        if ($referringRequest) {
            $originalRequest = clone $this->request;
            $this->request->setDispatched(false);

            $this->request->setControllerVendorName($referringRequest->getControllerVendorName());
            $this->request->setControllerVendorName($referringRequest->getControllerVendorName());
            $this->request->setControllerExtensionName($referringRequest->getControllerExtensionName());
            $this->request->setControllerName($referringRequest->getControllerName());
            $this->request->setControllerActionName($referringRequest->getControllerActionName());
            $this->request->setArguments($this->processor->getRequest()->getArguments());
            $this->request->setOriginalRequest($originalRequest);

            throw new StopActionException;
        } else {
            // @todo
            // @see \TYPO3\CMS\Extbase\Mvc\Controller\ActionController::forwardToReferringRequest()
        }
    }

    /**
     * If an exception of type `RedirectException` was thrown, the request is
     * forwarded, using the arguments sent to the exception.
     *
     * @param RedirectException $redirectException
     */
    protected function redirectFromException(RedirectException $redirectException)
    {
        $this->uriBuilder->setRequest($this->processor->getRequest());

        $this->redirect(
            $redirectException->getActionName(),
            $redirectException->getControllerName(),
            $redirectException->getExtensionName(),
            $redirectException->getArguments(),
            $redirectException->getPageUid(),
            $redirectException->getDelay(),
            $redirectException->getStatusCode()
        );
    }

    /**
     * @param ControllerProcessor $processor
     */
    public function injectProcessor(ControllerProcessor $processor)
    {
        $this->processor = $processor;
    }
}
