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
use Romm\Formz\Core\Core;
use Romm\Formz\Error\AjaxResult;
use Romm\Formz\Error\FormzMessageInterface;
use Romm\Formz\Exceptions\ClassNotFoundException;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Exceptions\InvalidArgumentTypeException;
use Romm\Formz\Exceptions\InvalidConfigurationException;
use Romm\Formz\Exceptions\MissingArgumentException;
use Romm\Formz\Form\Definition\Field\Validation\Validator;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Form\FormObject\FormObjectFactory;
use Romm\Formz\Service\ContextService;
use Romm\Formz\Service\ExtensionService;
use Romm\Formz\Service\MessageService;
use Romm\Formz\Validation\Field\DataObject\ValidatorDataObject;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Mvc\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Response;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;

class AjaxValidationController extends ActionController
{
    const DEFAULT_ERROR_MESSAGE_KEY = 'default_error_message';

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var bool
     */
    protected $protectedRequestMode = true;

    /**
     * @var string
     */
    protected $formClassName;

    /**
     * @var string
     */
    protected $formName;

    /**
     * @var string
     */
    protected $fieldName;

    /**
     * @var string
     */
    protected $validatorName;

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var FormObject
     */
    protected $formObject;

    /**
     * @var AjaxResult
     */
    protected $result;

    /**
     * @var Validator
     */
    protected $validation;

    /**
     * The only accepted method for the request is `POST`.
     */
    public function initializeAction()
    {
        if ($this->request->getMethod() !== 'POST') {
            $this->throwStatus(400);
        }
    }

    /**
     * Will process the request, but also prevent any external message to be
     * displayed, and catch any exception that could occur during the
     * validation.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @throws Exception
     */
    public function processRequest(RequestInterface $request, ResponseInterface $response)
    {
        $this->result = new AjaxResult;

        try {
            $this->processRequestParent($request, $response);
        } catch (Exception $exception) {
            if (false === $this->protectedRequestMode) {
                throw $exception;
            }

            $this->result->clear();

            $errorMessage = ExtensionService::get()->isInDebugMode()
                ? $this->getDebugMessageForException($exception)
                : ContextService::get()->translate(self::DEFAULT_ERROR_MESSAGE_KEY);

            $error = new Error($errorMessage, 1490176818);
            $this->result->addError($error);
            $this->result->setData('errorCode', $exception->getCode());
        }

        // Cleaning every external message.
        ob_clean();

        $this->injectResultInResponse();
    }

    /**
     * Will take care of adding a new argument to the request, based on the form
     * name and the form class name found in the request arguments.
     */
    protected function initializeActionMethodValidators()
    {
        $this->initializeActionMethodValidatorsParent();

        $request = $this->getRequest();

        if (false === $request->hasArgument('name')) {
            throw MissingArgumentException::ajaxControllerNameArgumentNotSet();
        }

        if (false === $request->hasArgument('className')) {
            throw MissingArgumentException::ajaxControllerClassNameArgumentNotSet();
        }

        $className = $request->getArgument('className');

        if (false === class_exists($className)) {
            throw ClassNotFoundException::ajaxControllerFormClassNameNotFound($className);
        }

        if (false === in_array(FormInterface::class, class_implements($className))) {
            throw InvalidArgumentTypeException::ajaxControllerWrongFormType($className);
        }

        $this->arguments->addNewArgument($request->getArgument('name'), $className, true);
    }

    /**
     * Main action that will process the field validation.
     *
     * @param string $name
     * @param string $className
     * @param string $fieldName
     * @param string $validatorName
     */
    public function runAction($name, $className, $fieldName, $validatorName)
    {
        $this->formName = $name;
        $this->formClassName = $className;
        $this->fieldName = $fieldName;
        $this->validatorName = $validatorName;
        $this->form = $this->getForm();

        $this->formObject = $this->getFormObject();

        $this->validation = $this->getFieldValidation();

        $validatorDataObject = new ValidatorDataObject($this->formObject, $this->validation);

        /** @var ValidatorInterface $validator */
        $validator = GeneralUtility::makeInstance(
            $this->validation->getClassName(),
            $this->validation->getOptions(),
            $validatorDataObject
        );

        $fieldValue = ObjectAccess::getProperty($this->form, $this->fieldName);
        $result = $validator->validate($fieldValue);

        $this->result->merge($result);
    }

    /**
     * @return Validator
     * @throws EntryNotFoundException
     * @throws InvalidConfigurationException
     */
    protected function getFieldValidation()
    {
        $validationResult = $this->formObject->getDefinitionValidationResult();

        if (true === $validationResult->hasErrors()) {
            throw InvalidConfigurationException::ajaxControllerInvalidFormConfiguration();
        }

        $formConfiguration = $this->formObject->getDefinition();

        if (false === $formConfiguration->hasField($this->fieldName)) {
            throw EntryNotFoundException::ajaxControllerFieldNotFound($this->fieldName, $this->formObject);
        }

        $field = $formConfiguration->getField($this->fieldName);

        if (false === $field->hasValidator($this->validatorName)) {
            throw EntryNotFoundException::ajaxControllerValidationNotFoundForField($this->validatorName, $this->fieldName);
        }

        $fieldValidationConfiguration = $field->getValidator($this->validatorName);

        if (false === $fieldValidationConfiguration->doesUseAjax()) {
            throw InvalidConfigurationException::ajaxControllerAjaxValidationNotActivated($this->validatorName, $this->fieldName);
        }

        return $fieldValidationConfiguration;
    }

    /**
     * Fetches errors/warnings/notices in the result, and put them in the JSON
     * response.
     */
    protected function injectResultInResponse()
    {
        $validationName = $this->validation instanceof Validator
            ? $this->validation->getName()
            : 'default';

        $validationResult = MessageService::get()->sanitizeValidatorResult($this->result, $validationName);

        $result = [
            'success'  => !$this->result->hasErrors(),
            'data'     => $this->result->getData(),
            'messages' => [
                'errors'   => $this->formatMessages($validationResult->getErrors()),
                'warnings' => $this->formatMessages($validationResult->getWarnings()),
                'notices'  => $this->formatMessages($validationResult->getNotices())
            ]
        ];

        $this->setUpResponseResult($result);
    }

    /**
     * @param array $result
     */
    protected function setUpResponseResult(array $result)
    {
        $this->response->setHeader('Content-Type', 'application/json');
        $this->response->setContent(json_encode($result));

        Core::get()->getPageController()->setContentType('application/json');
    }

    /**
     * @param FormzMessageInterface[] $messages
     * @return array
     */
    protected function formatMessages(array $messages)
    {
        $sortedMessages = [];

        foreach ($messages as $message) {
            $sortedMessages[$message->getMessageKey()] = $message->getMessage();
        }

        return $sortedMessages;
    }

    /**
     * Wrapper for unit tests.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     */
    protected function processRequestParent(RequestInterface $request, ResponseInterface $response)
    {
        parent::processRequest($request, $response);
    }

    /**
     * Wrapper for unit tests.
     */
    protected function initializeActionMethodValidatorsParent()
    {
        parent::initializeActionMethodValidators();
    }

    /**
     * Used in unit testing.
     *
     * @param bool $flag
     */
    public function setProtectedRequestMode($flag)
    {
        $this->protectedRequestMode = (bool)$flag;
    }

    /**
     * @param Exception $exception
     * @return string
     */
    protected function getDebugMessageForException(Exception $exception)
    {
        return 'Debug mode – ' . $exception->getMessage();
    }

    /**
     * @return FormInterface
     * @throws MissingArgumentException
     */
    protected function getForm()
    {
        return $this->arguments->getArgument($this->formName)->getValue();
    }

    /**
     * @return FormObject
     */
    protected function getFormObject()
    {
        return FormObjectFactory::get()->registerAndGetFormInstance($this->form, $this->formName);
    }

    /**
     * Wrapper for unit tests.
     *
     * @return Request
     */
    protected function getRequest()
    {
        return $this->request;
    }
}
