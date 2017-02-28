<?php
/*
 * 2017 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Formz project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\Controller;

use Romm\Formz\Configuration\Form\Field\Validation\Validation;
use Romm\Formz\Configuration\Form\Form;
use Romm\Formz\Core\Core;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Exceptions\InvalidConfigurationException;
use Romm\Formz\Exceptions\MissingArgumentException;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObject;
use Romm\Formz\Form\FormObjectFactory;
use Romm\Formz\Service\ContextService;
use Romm\Formz\Service\ExtensionService;
use Romm\Formz\Service\MessageService;
use Romm\Formz\Validation\DataObject\ValidatorDataObject;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Message;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\JsonView;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;

class AjaxValidationController extends ActionController
{
    const ARGUMENT_FORM_CLASS_NAME = 'formClassName';
    const ARGUMENT_FORM_NAME = 'formName';
    const ARGUMENT_FORM = 'form';
    const ARGUMENT_FIELD_NAME = 'fieldName';
    const ARGUMENT_VALIDATOR_NAME = 'validatorName';

    const DEFAULT_ERROR_MESSAGE_KEY = 'default_error_message';

    /**
     * @var array
     */
    public static $requiredArguments = [
        self::ARGUMENT_FORM_CLASS_NAME,
        self::ARGUMENT_FORM_NAME,
        self::ARGUMENT_FORM,
        self::ARGUMENT_FIELD_NAME,
        self::ARGUMENT_VALIDATOR_NAME
    ];

    /**
     * @var JsonView
     */
    protected $view;

    /**
     * @var string
     */
    protected $defaultViewObjectName = JsonView::class;

    /**
     * @var Request
     */
    protected $request;

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
     * @var array
     */
    protected $form;

    /**
     * @var string
     */
    protected $fieldName;

    /**
     * @var string
     */
    protected $validatorName;

    /**
     * @var FormObject
     */
    protected $formObject;

    /**
     * The only accepted method for the request is `POST`.
     */
    public function initializeAction()
    {
        if ($this->request->getMethod() !== 'POST') {
            $this->throwStatus(400);
        }
    }

    public function getView()
    {
        return $this->view;
    }

    /**
     * Main action that will render the validation result.
     */
    public function runAction()
    {
        $result = ($this->protectedRequestMode)
            ? $this->getProtectedRequestResult()
            : $this->getRequestResult();

        $this->view->setVariablesToRender(['result']);
        $this->view->assign('result', $result);
    }

    /**
     * @param bool $flag
     */
    public function setProtectedRequestMode($flag)
    {
        $this->protectedRequestMode = (bool)$flag;
    }

    /**
     * Will fetch the result and prevent any exception or external message to be
     * displayed.
     *
     * @return array
     */
    protected function getProtectedRequestResult()
    {
        // Default technical error result if the function can not be reached.
        $result = [
            'success' => false,
            'messages' => [
                'errors' => ['default' => ContextService::get()->translate(self::DEFAULT_ERROR_MESSAGE_KEY)]
            ]
        ];

        // We prevent any external message to be displayed here.
        ob_start();

        try {
            $result = $this->getRequestResult();
        } catch (\Exception $exception) {
            $result['data'] = ['errorCode' => $exception->getCode()];

            if (ExtensionService::get()->isInDebugMode()) {
                $result['messages']['errors']['default'] = $this->getDebugMessageForException($exception);
            }
        }

        ob_end_clean();

        return $result;
    }

    /**
     * Will get the result of the validation for this Ajax request.
     *
     * If any error is found, an exception is thrown.
     *
     * @return array
     */
    protected function getRequestResult()
    {
        $this->initializeArguments();

        $this->formObject = $this->getFormObject();
        $this->checkConfigurationValidationResult();
        $validation = $this->getFieldValidation();
        $form = $this->buildFormObject();
        $fieldValue = ObjectAccess::getProperty($form, $this->fieldName);
        $validatorDataObject = new ValidatorDataObject($this->formObject, $form, $validation);

        /** @var ValidatorInterface $validator */
        $validator = GeneralUtility::makeInstance(
            $validation->getClassName(),
            $validation->getOptions(),
            $validatorDataObject
        );

        return $this->convertResultToJson($validator->validate($fieldValue));
    }

    /**
     * Initializes all arguments for the request, and returns an array
     * containing the missing arguments.
     */
    protected function initializeArguments()
    {
        $argumentsMissing = [];

        foreach (self::$requiredArguments as $argument) {
            $argumentValue = $this->getArgument($argument);

            if ($argumentValue) {
                $this->$argument = $argumentValue;
            } else {
                $argumentsMissing[] = $argument;
            }
        }

        if (false === empty($argumentsMissing)) {
            throw new MissingArgumentException(
                'One or more arguments are missing in the request: "' . implode('", "', $argumentsMissing) . '".',
                1487673983
            );
        }
    }

    /**
     * @return FormObject
     */
    protected function getFormObject()
    {
        /** @var FormObjectFactory $formObjectFactory */
        $formObjectFactory = Core::instantiate(FormObjectFactory::class);

        return $formObjectFactory->getInstanceFromClassName($this->formClassName, $this->formName);
    }

    /**
     * @throws InvalidConfigurationException
     */
    protected function checkConfigurationValidationResult()
    {
        $validationResult = $this->formObject->getConfigurationValidationResult();

        if (true === $validationResult->hasErrors()) {
            throw new InvalidConfigurationException(
                'The form configuration contains errors.',
                1487671395
            );
        }
    }

    /**
     * @return Validation
     * @throws EntryNotFoundException
     * @throws InvalidConfigurationException
     */
    protected function getFieldValidation()
    {
        $formConfiguration = $this->getFormConfiguration($this->formObject);
        $field = $formConfiguration->getField($this->fieldName);

        if (false === $field->hasValidation($this->validatorName)) {
            throw new EntryNotFoundException(
                'The field "' . $this->fieldName . '" does not have a rule "' . $this->validatorName . '".',
                1487672956
            );
        }

        $fieldValidationConfiguration = $field->getValidationByName($this->validatorName);

        if (false === $fieldValidationConfiguration->doesUseAjax()) {
            throw new InvalidConfigurationException(
                'The validation "' . $this->validatorName . '" of the field "' . $this->fieldName . '" is not configured to work with Ajax. Please add the option "useAjax".',
                1487673434
            );
        }

        return $fieldValidationConfiguration;
    }

    /**
     * @param FormObject $formObject
     * @return Form
     * @throws EntryNotFoundException
     */
    protected function getFormConfiguration(FormObject $formObject)
    {
        $formConfiguration = $formObject->getConfiguration();

        if (false === $formConfiguration->hasField($this->fieldName)) {
            throw new EntryNotFoundException(
                'The field "' . $this->fieldName . '" was not found in the form "' . $this->formName . '" with class "' . $this->formClassName . '".',
                1487671603
            );
        }

        return $formConfiguration;
    }

    /**
     * Will build and fill an object with a form sent value.
     *
     * @return FormInterface
     */
    protected function buildFormObject()
    {
        $values = $this->cleanValuesFromUrl($this->form);

        return $this->getPropertyMapper()->convert($values, $this->formClassName);
    }

    /**
     * Will convert the result of the function called by this class in a JSON
     * string.
     *
     * @param Result $result
     * @return array
     */
    protected function convertResultToJson(Result $result)
    {
        $messages = [];

        if ($result->hasErrors()) {
            $messages['errors'] = $this->formatMessages($result->getErrors());
        }

        if ($result->hasWarnings()) {
            $messages['warnings'] = $this->formatMessages($result->getWarnings());
        }

        if ($result->hasNotices()) {
            $messages['notices'] = $this->formatMessages($result->getNotices());
        }

        return [
            'success' => !$result->hasErrors(),
            'messages' => $messages
        ];
    }

    /**
     * @param Message[] $messages
     * @return array
     */
    protected function formatMessages(array $messages)
    {
        $sortedMessages = [];

        foreach ($messages as $message) {
            $key = MessageService::get()->getMessageKey($message);
            $sortedMessages[$key] = $message->getMessage();
        }

        return $sortedMessages;
    }

    /**
     * @param \Exception $exception
     * @return string
     */
    protected function getDebugMessageForException(\Exception $exception)
    {
        return 'Debug mode – ' . $exception->getMessage();
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function getArgument($name)
    {
        return GeneralUtility::_GP($name);
    }

    /**
     * Will clean the string filled with form values sent with Ajax.
     *
     * @param array $values
     * @return array
     */
    protected function cleanValuesFromUrl($values)
    {
        // Cleaning the given form values.
        $values = reset($values);
        unset($values['__referrer']);
        unset($values['__trustedProperties']);

        return reset($values);
    }

    /**
     * @return PropertyMapper
     */
    protected function getPropertyMapper()
    {
        /** @var PropertyMapper $propertyMapper */
        $propertyMapper = Core::instantiate(PropertyMapper::class);

        return $propertyMapper;
    }
}
