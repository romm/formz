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

namespace Romm\Formz\Validation;

use Romm\Formz\Configuration\Form\Field\Validation\Validation;
use Romm\Formz\Configuration\Form\Form;
use Romm\Formz\Core\Core;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Exceptions\InvalidArgumentValueException;
use Romm\Formz\Exceptions\InvalidConfigurationException;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObject;
use Romm\Formz\Form\FormObjectFactory;
use Romm\Formz\Service\ContextService;
use Romm\Formz\Service\ExtensionService;
use Romm\Formz\Validation\DataObject\ValidatorDataObject;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;

/**
 * This class is used for automatic Ajax calls for fields which have the setting
 * `useAjax` enabled.
 */
class AjaxFieldValidation implements SingletonInterface
{
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
    protected $fieldValue;

    /**
     * @var string
     */
    protected $fieldName;

    /**
     * @var string
     */
    protected $validatorName;

    /**
     * Main function called.
     */
    public function run()
    {
        // Default technical error result if the function can not be reached.
        $result = [
            'success' => false,
            'message' => [ContextService::get()->translate('default_error_message')]
        ];

        // We prevent any external message to be displayed here.
        ob_start();

        try {
            $result = $this->getRequestResult();
        } catch (\Exception $e) {
            $result['data'] = ['errorCode' => $e->getCode()];

            if (ExtensionService::get()->isInDebugMode()) {
                $result['message'] = 'Debug mode – ' . $e->getMessage();
            }
        }

        ob_end_clean();

        return json_encode($result);
    }

    /**
     * @param FormObject $formObject
     * @throws InvalidConfigurationException
     */
    protected function checkConfigurationValidationResult(FormObject $formObject)
    {
        $validationResult = $formObject->getConfigurationValidationResult();

        if (true === $validationResult->hasErrors()) {
            throw new InvalidConfigurationException(
                'The form configuration contains errors.',
                1487671395
            );
        }
    }

    /**
     * Will get the result of the validation for this Ajax request.
     *
     * If any error is found, an exception is thrown.
     *
     * @return array
     * @throws EntryNotFoundException
     * @throws InvalidArgumentValueException
     * @throws InvalidConfigurationException
     */
    protected function getRequestResult()
    {
        $this->initializeArguments();

        /** @var FormObjectFactory $formObjectFactory */
        $formObjectFactory = Core::instantiate(FormObjectFactory::class);
        $formObject = $formObjectFactory->getInstanceFromClassName($this->formClassName, $this->formName);

        $this->checkConfigurationValidationResult($formObject);
        $validation = $this->getFieldValidation($formObject);
        $validatorClassName = $this->getValidatorClassName($validation);

        $form = $this->buildObject();
        $this->fieldValue = ObjectAccess::getProperty($form, $this->fieldName);

        $validatorDataObject = new ValidatorDataObject($form, $validation);

        /** @var ValidatorInterface $validator */
        $validator = GeneralUtility::makeInstance(
            $validatorClassName,
            $validation->getOptions(),
            $validatorDataObject
        );

        return $this->convertResultToJson($validator->validate($this->fieldValue));
    }

    /**
     * Initializes all arguments for the request, and returns an array
     * containing the missing arguments.
     */
    protected function initializeArguments()
    {
        $arguments = ['formClassName', 'formName', 'fieldValue', 'fieldName', 'validatorName'];
        $argumentsMissing = [];

        foreach ($arguments as $argument) {
            $argumentValue = GeneralUtility::_GP($argument);

            if ($argumentValue) {
                $this->$argument = $argumentValue;
            } else {
                $argumentsMissing[] = $argument;
            }
        }

        if (false === empty($argumentsMissing)) {
            throw new InvalidArgumentValueException(
                'One or more arguments are missing in the request: "' . implode('", "', $argumentsMissing) . '".',
                1487673983
            );
        }
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
     * @param FormObject $formObject
     * @return Validation
     * @throws EntryNotFoundException
     * @throws InvalidConfigurationException
     */
    protected function getFieldValidation(FormObject $formObject)
    {
        $formConfiguration = $this->getFormConfiguration($formObject);
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
     * @param Validation $fieldValidationConfiguration
     * @return string
     * @throws InvalidConfigurationException
     */
    protected function getValidatorClassName(Validation $fieldValidationConfiguration)
    {
        $validatorClassName = $fieldValidationConfiguration->getClassName();

        if (false === in_array(ValidatorInterface::class, class_implements($validatorClassName))) {
            throw new InvalidConfigurationException(
                'The class name "' . $validatorClassName . '" of the validation "' . $this->validatorName . '" of the field "' . $this->fieldName . '" must implement the interface "' . ValidatorInterface::class . '".',
                1487673690
            );
        }

        return $validatorClassName;
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
        $error = ($result->hasErrors())
            ? $result->getFirstError()->getMessage()
            : '';

        return [
            'success' => !$result->hasErrors(),
            'message' => $error
        ];
    }

    /**
     * Will build and fill an object with a form sent value.
     *
     * @return FormInterface
     */
    protected function buildObject()
    {
        $values = $this->cleanValuesFromUrl($this->fieldValue);
        /** @var ReflectionService $reflectionService */
        $reflectionService = Core::instantiate(ReflectionService::class);
        /** @var FormInterface $object */
        $object = Core::instantiate($this->formClassName);
        $properties = $reflectionService->getClassPropertyNames($this->formClassName);

        foreach ($properties as $propertyName) {
            if (ObjectAccess::isPropertySettable($object, $propertyName)
                && isset($values[$propertyName])
            ) {
                ObjectAccess::setProperty($object, $propertyName, $values[$propertyName]);
            }
        }

        return $object;
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
}
