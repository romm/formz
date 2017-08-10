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

namespace Romm\Formz\Controller\Processor;

use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Form\FormObject\FormObjectFactory;
use Romm\Formz\Service\Traits\ExtendedSelfInstantiateTrait;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\Argument;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Request as MvcRequest;
use TYPO3\CMS\Extbase\Mvc\Web\Request;

class ControllerProcessor implements SingletonInterface
{
    use ExtendedSelfInstantiateTrait;

    /**
     * @var FormObjectFactory
     */
    protected $formObjectFactory;

    /**
     * @var FormObject[]
     */
    protected $formArguments;

    /**
     * @var bool
     */
    protected $dispatched = false;

    /**
     * @var Request
     */
    protected $originalRequest;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Arguments
     */
    protected $requestArguments;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @todo
     *
     * @var string
     */
    protected $lastDispatchedRequest;

    /**
     * @param MvcRequest $request
     * @param Arguments  $requestArguments
     * @param array      $settings
     * @return $this
     */
    public static function prepare(MvcRequest $request, Arguments $requestArguments, array $settings)
    {
        return self::get()->setData($request, $requestArguments, $settings);
    }

    /**
     * Injects the data needed for this class to work properly. This method must
     * be called before the dispatch is called.
     *
     * @param MvcRequest $request
     * @param Arguments  $requestArguments
     * @param array      $settings
     * @return $this
     */
    public function setData(MvcRequest $request, Arguments $requestArguments, array $settings)
    {
        /** @var Request $request */
        $dispatchedRequest = $request->getControllerObjectName() . '::' . $request->getControllerActionName();

        if ($dispatchedRequest !== $this->lastDispatchedRequest) {
            $this->lastDispatchedRequest = $dispatchedRequest;

            $this->originalRequest = $request;
            $this->request = clone $request;
            $this->requestArguments = $requestArguments;
            $this->settings = $settings;
            $this->formArguments = null;
            $this->dispatched = false;
        }

        return $this;
    }

    /**
     * Will dispatch the current request to the form controller, which will take
     * care of processing everything properly.
     *
     * In case no form is found in the controller action parameters, the current
     * request is not killed.
     *
     * @throws StopActionException
     */
    public function dispatch()
    {
        if (false === $this->dispatched) {
            $this->dispatched = true;

            $this->doDispatch();
        }
    }

    /**
     * Wrapper for unit testing.
     */
    protected function doDispatch()
    {
        if (false === empty($this->getRequestForms())) {
            $this->originalRequest->setDispatched(false);
            $this->originalRequest->setControllerVendorName('Romm');
            $this->originalRequest->setControllerExtensionName('Formz');
            $this->originalRequest->setControllerName('Form');
            $this->originalRequest->setControllerActionName('processForm');

            $this->checkFormObjectsErrors();

            throw new StopActionException;
        }
    }

    /**
     * Will check if the form objects found in the request arguments contain
     * configuration errors. If they do, we dispatch the request to the error
     * view, where all errors will be explained properly to the user.
     */
    protected function checkFormObjectsErrors()
    {
        foreach ($this->getRequestForms() as $formObject) {
            if ($formObject->getDefinitionValidationResult()->hasErrors()) {
                $this->originalRequest->setControllerActionName('formObjectError');
                $this->originalRequest->setArguments(['formObject' => $formObject]);

                break;
            }
        }
    }

    /**
     * Loops on the request arguments, and pick up each one that is a form
     * instance (it implements `FormInterface`).
     *
     * @return FormObject[]
     */
    public function getRequestForms()
    {
        if (null === $this->formArguments) {
            $this->formArguments = [];

            /** @var Argument $argument */
            foreach ($this->requestArguments as $argument) {
                $type = $argument->getDataType();

                if (class_exists($type)
                    && in_array(FormInterface::class, class_implements($type))
                ) {
                    $formClassName = $argument->getDataType();
                    $formName = $argument->getName();

                    $formObject = $this->formObjectFactory->getInstanceWithClassName($formClassName, $formName);
                    $this->formArguments[$formName] = $formObject;
                }
            }
        }

        return $this->formArguments;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Arguments
     */
    public function getRequestArguments()
    {
        return $this->requestArguments;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param FormObjectFactory $formObjectFactory
     */
    public function injectFormObjectFactory(FormObjectFactory $formObjectFactory)
    {
        $this->formObjectFactory = $formObjectFactory;
    }
}
