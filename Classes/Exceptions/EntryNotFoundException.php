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

namespace Romm\Formz\Exceptions;

use Romm\Formz\Configuration\Form\Condition\Activation\AbstractActivation;
use Romm\Formz\Form\FormObject;
use Romm\Formz\Validation\Validator\AbstractValidator;
use Romm\Formz\ViewHelpers\ClassViewHelper;
use Romm\Formz\ViewHelpers\FieldViewHelper;
use Romm\Formz\ViewHelpers\FormatMessageViewHelper;

class EntryNotFoundException extends FormzException
{
    const FIELD_NOT_FOUND = 'The field "%s" was not found in the form "%s" with class "%s".';

    const CONDITION_NOT_FOUND = 'No condition "%s" was found.';

    const VALIDATION_NOT_FOUND = 'The validation "%s" was not found. Please use the function `%s::hasValidation()` before.';

    const VALIDATION_NOT_FOUND_FOR_FIELD = 'The field "%s" does not have a rule "%s".';

    const ERROR_KEY_NOT_FOUND_FOR_VALIDATOR = 'The error key "%s" does not exist for the validator "%s".';

    const VIEW_HELPER_FIELD_NOT_FOUND = 'The field "%s" could not be fetched for the view helper "%s": please either use this view helper inside the view helper "%s", or fill the parameter `field` of this view helper with the field name you want.';

    const FIELD_VIEW_HELPER_LAYOUT_NOT_FOUND = 'The layout "%s" could not be found. Please check your TypoScript configuration.';

    const FIELD_VIEW_HELPER_LAYOUT_ITEM_NOT_FOUND = 'The layout "%s" does not have an item "%s".';

    const FORM_VIEW_HELPER_CONTROLLER_ACTION_ARGUMENT_MISSING = 'The method `%s::%s()` must have a parameter `$%s`. Note that you can also change the parameter `name` of the form view helper.';

    const SLOT_NOT_FOUND = 'No slot "%s" was found.';

    /**
     * @code 1488482191
     *
     * @param string $name
     * @return self
     */
    final public static function conditionNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::CONDITION_NOT_FOUND,
            [$name]
        );

        return $exception;
    }

    /**
     * @code 1487672276
     *
     * @param string $name
     * @return self
     */
    final public static function validationNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::VALIDATION_NOT_FOUND,
            [$name, AbstractActivation::class]
        );

        return $exception;
    }

    /**
     * @code 1487672956
     *
     * @param string $validationName
     * @param string $fieldName
     * @return EntryNotFoundException
     */
    final public static function ajaxControllerValidationNotFoundForField($validationName, $fieldName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::VALIDATION_NOT_FOUND_FOR_FIELD,
            [$fieldName, $validationName]
        );

        return $exception;
    }

    /**
     * @code 1487671603
     *
     * @param string     $fieldName
     * @param FormObject $formObject
     * @return EntryNotFoundException
     */
    final public static function ajaxControllerFieldNotFound($fieldName, FormObject $formObject)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_NOT_FOUND,
            [$fieldName, $formObject->getName(), $formObject->getClassName()]
        );

        return $exception;
    }

    /**
     * @code 1455272659
     *
     * @param string            $key
     * @param AbstractValidator $validator
     * @return EntryNotFoundException
     */
    final public static function errorKeyNotFoundForValidator($key, AbstractValidator $validator)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::ERROR_KEY_NOT_FOUND_FOR_VALIDATOR,
            [$key, get_class($validator)]
        );

        return $exception;
    }

    /**
     * @code 1487947224
     *
     * @param string     $fieldName
     * @param FormObject $formObject
     * @return EntryNotFoundException
     */
    final public static function equalsToFieldValidatorFieldNotFound($fieldName, FormObject $formObject)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_NOT_FOUND,
            [$fieldName, $formObject->getName(), $formObject->getClassName()]
        );

        return $exception;
    }

    /**
     * @code 1467623761
     *
     * @param string $fieldName
     * @return self
     */
    final public static function classViewHelperFieldNotFound($fieldName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::VIEW_HELPER_FIELD_NOT_FOUND,
            [$fieldName, ClassViewHelper::class, FieldViewHelper::class]
        );

        return $exception;
    }

    /**
     * @code 1467624152
     *
     * @param string $fieldName
     * @return self
     */
    final public static function formatMessageViewHelperFieldNotFound($fieldName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::VIEW_HELPER_FIELD_NOT_FOUND,
            [$fieldName, FormatMessageViewHelper::class, FieldViewHelper::class]
        );

        return $exception;
    }

    /**
     * @code 1465243586
     *
     * @param string $layoutName
     * @return self
     */
    final public static function fieldViewHelperLayoutNotFound($layoutName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_VIEW_HELPER_LAYOUT_NOT_FOUND,
            [$layoutName]
        );

        return $exception;
    }

    /**
     * @code 1485867803
     *
     * @param string $layoutName
     * @param string $itemName
     * @return EntryNotFoundException
     */
    final public static function fieldViewHelperLayoutItemNotFound($layoutName, $itemName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_VIEW_HELPER_LAYOUT_ITEM_NOT_FOUND,
            [$layoutName, $itemName]
        );

        return $exception;
    }

    /**
     * @code 1473084335
     *
     * @param string     $fieldName
     * @param FormObject $formObject
     * @return EntryNotFoundException
     */
    final public static function formatMessageViewHelperFieldNotFoundInForm($fieldName, FormObject $formObject)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_NOT_FOUND,
            [$fieldName, $formObject->getName(), $formObject->getClassName()]
        );

        return $exception;
    }

    /**
     * @code 1457441846
     *
     * @param string $controllerObjectName
     * @param string $actionName
     * @param string $formName
     * @return EntryNotFoundException
     */
    final public static function formViewHelperControllerActionArgumentMissing($controllerObjectName, $actionName, $formName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FORM_VIEW_HELPER_CONTROLLER_ACTION_ARGUMENT_MISSING,
            [$controllerObjectName, $actionName, $formName]
        );

        return $exception;
    }

    /**
     * @code 1488988452
     *
     * @param string $name
     * @return self
     */
    final public static function slotNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::SLOT_NOT_FOUND,
            [$name]
        );

        return $exception;
    }
}
