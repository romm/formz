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

use Romm\Formz\Core\Core;
use Romm\Formz\Form\FormInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * This class is used for automatic Ajax calls for fields which have the setting
 * `useAjax` set to `true`.
 */
class AjaxFieldValidation implements SingletonInterface
{

    /**
     * Main function called.
     */
    public function run()
    {
        // Default technical error result if the function can not be reached.
        $result = [
            'success' => false,
            'message' => [Core::get()->translate('default_error_message')]
        ];

        // We prevent any external message to be displayed here.
        ob_start();

        // Getting the sent arguments.
        $formClassName = GeneralUtility::_GP('formClassName');
        $formName = GeneralUtility::_GP('formName');
        $passObjectInstance = GeneralUtility::_GP('passObjectInstance');
        $fieldValue = GeneralUtility::_GP('fieldValue');
        $fieldName = GeneralUtility::_GP('fieldName');
        $validatorName = GeneralUtility::_GP('validatorName');

        if ($formClassName && $formName && $passObjectInstance && $fieldValue && $fieldName && $validatorName) {
            try {
                $formObject = Core::get()->getFormObjectFactory()->getInstanceFromClassName($formClassName, $formName);
                $validationResult = $formObject->getConfigurationValidationResult();

                if (false === $validationResult->hasErrors()) {
                    $formConfiguration = $formObject->getConfiguration();

                    if (true === $formConfiguration->hasField($fieldName)) {
                        $fieldValidationConfiguration = $formConfiguration->getField($fieldName)->getValidation($validatorName);
                        $validatorClassName = $fieldValidationConfiguration->getClassName();

                        if (null !== $fieldValidationConfiguration
                            && true === $fieldValidationConfiguration->doesUseAjax()
                            && class_exists($validatorClassName)
                            && in_array(AbstractValidator::class, class_parents($validatorClassName))
                        ) {
                            $form = null;
                            if ('true' === $passObjectInstance) {
                                $form = $this->buildObject($formClassName, $this->cleanValuesFromUrl($fieldValue));
                                $fieldValue = ObjectAccess::getProperty($form, $fieldName);
                            }

                            /** @var AbstractValidator $validatorInstance */
                            /** @noinspection PhpMethodParametersCountMismatchInspection */
                            $validatorInstance = Core::get()->getObjectManager()->get(
                                $validatorClassName,
                                $fieldValidationConfiguration->getOptions(),
                                $form,
                                $fieldName,
                                $fieldValidationConfiguration->getMessages()
                            );
                            $result = $this->convertResultToJson($validatorInstance->validate($fieldValue));
                        }
                    }
                }
            } catch (\Exception $e) {
                $result['data'] = ['errorCode' => $e->getCode()];
                if (Core::get()->isInDebugMode()) {
                    $result['message'] = $e->getMessage();
                }
            }
        }

        ob_end_clean();

        return json_encode($result);
    }

    /**
     * Will clean the string filled with form values sent with Ajax.
     *
     * @param string $values
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
     * @param string $className Class name of the model object.
     * @param array  $values    Values for properties.
     * @return FormInterface
     */
    protected function buildObject($className, array $values)
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var ReflectionService $reflectionService */
        $reflectionService = $objectManager->get(ReflectionService::class);

        /** @var FormInterface $object */
        $object = $objectManager->get($className);

        $properties = $reflectionService->getClassPropertyNames($className);

        foreach ($properties as $propertyName) {
            if (ObjectAccess::isPropertySettable($object, $propertyName)
                && isset($values[$propertyName])
            ) {
                ObjectAccess::setProperty($object, $propertyName, $values[$propertyName]);
            }
        }

        return $object;
    }
}
