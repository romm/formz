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
use Romm\Formz\Form\FormObject\FormObjectFactory;
use Romm\Formz\Middleware\Processor\MiddlewareProcessor;
use Romm\Formz\Middleware\Request\Exception\ForwardException;
use Romm\Formz\Middleware\Request\Exception\RedirectException;
use Romm\Formz\Middleware\Request\Exception\StopPropagationException;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;

/**
 * This is the main form controller, which is called before a controller action
 * with at least form argument is called.
 *
 * It will process the argument form(s), for instance by calling all its
 * middlewares. It allows manipulating the request, and information about the
 * form, before the actual action is called.
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
        $exception = null;

        try {
            $this->invokeMiddlewares();
            $this->manageRequestResult();
        } catch (Exception $exception) {
        }

        $this->persistForms();

        if ($exception instanceof Exception) {
            if ($exception instanceof StopPropagationException) {
                if ($exception instanceof RedirectException) {
                    $this->redirectFromException($exception);
                } elseif (false === $exception instanceof ForwardException) {
                    $this->resetSubstepsLevel();
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
     * middleware that was registered in its TypoScript configuration.
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
     * @todo
     */
    protected function resetSubstepsLevel()
    {
        foreach ($this->processor->getRequestForms() as $formObject) {
            $stepService = FormObjectFactory::get()->getStepService($formObject);
            $stepService->setSubstepsLevel(1);
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
     * Loops on every form of this request, and persists each one.
     */
    protected function persistForms()
    {
        foreach ($this->processor->getRequestForms() as $formObject) {
            if ($formObject->hasForm()
                && ($formObject->isPersistent()
                    || $formObject->formWasSubmitted()
                )
            ) {
                $formObject->getPersistenceManager()->save();

                if ($formObject->isPersistent()) {
                    $formObject->getFormMetadata()->persist();
                }
            }
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
        $request = $this->processor->getRequest();

        $this->request->setPluginName($request->getPluginName());
        $this->request->setControllerVendorName($request->getControllerVendorName());
        $this->request->setControllerExtensionName($request->getControllerExtensionName());
        $this->request->setControllerName($request->getControllerName());
        $this->request->setControllerActionName($request->getControllerActionName());
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
        $originalRequest = $this->processor->getRequest();
        $referringRequest = $originalRequest->getReferringRequest();

        if ($referringRequest) {
            $this->request->setDispatched(false);
            $this->request->setOriginalRequest($originalRequest);

            $this->request->setControllerVendorName($referringRequest->getControllerVendorName());
            $this->request->setControllerExtensionName($referringRequest->getControllerExtensionName());
            $this->request->setControllerName($referringRequest->getControllerName());
            $this->request->setControllerActionName($referringRequest->getControllerActionName());
            $this->request->setArguments($this->processor->getRequest()->getArguments());
        } else {
            // @todo ?
        }

        throw new StopActionException;
    }

    /**
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
