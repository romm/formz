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

use Romm\Formz\Configuration\Form\Field\Activation\AbstractActivation;
use Romm\Formz\Configuration\Form\Form;
use Romm\Formz\Configuration\View\Classes\ViewClass;
use Romm\Formz\Configuration\View\Layouts\LayoutGroup;
use Romm\Formz\Configuration\View\View;
use Romm\Formz\Form\FormObject;
use Romm\Formz\Validation\Validator\AbstractValidator;
use Romm\Formz\ViewHelpers\ClassViewHelper;
use Romm\Formz\ViewHelpers\FieldViewHelper;
use Romm\Formz\ViewHelpers\FormatMessageViewHelper;

class EntryNotFoundException extends FormzException
{
    const FIELD_NOT_FOUND = 'The field "%s" was not found in the form "%s" with class "%s".';

    const CONDITION_NOT_FOUND = 'Trying to access a condition which is not registered: "%s". Here is a list of all currently registered conditions: "%s".';

    const ACTIVATION_CONDITION_NOT_FOUND = 'No condition "%s" was found.';

    const CONFIGURATION_FIELD_NOT_FOUND = 'The field "%s" was not found. Please use the function `%s::hasField()` before.';

    const VALIDATION_NOT_FOUND = 'The validation "%s" was not found. Please use the function `%s::hasValidation()` before.';

    const VIEW_LAYOUT_NOT_FOUND = 'The layout "%s" was not found. Please use the function `%s::hasLayout()` before.';

    const VIEW_LAYOUT_ITEM_NOT_FOUND = 'The layout item "%s" was not found. Please use the function `%s::hasItem()` before.';

    const VIEW_CLASS_NOT_FOUND = 'The class "%s" was not found. Please use the function `%s::hasItem()` before.';

    const VALIDATION_NOT_FOUND_FOR_FIELD = 'The field "%s" does not have a rule "%s".';

    const ERROR_KEY_NOT_FOUND_FOR_VALIDATOR = 'The error key "%s" does not exist for the validator "%s".';

    const VIEW_HELPER_FIELD_NOT_FOUND = 'The field "%s" could not be fetched for the view helper "%s": please either use this view helper inside the view helper "%s", or fill the parameter `field` of this view helper with the field name you want.';

    const FIELD_VIEW_HELPER_LAYOUT_NOT_FOUND = 'The layout "%s" could not be found. Please check your TypoScript configuration.';

    const FIELD_VIEW_HELPER_LAYOUT_ITEM_NOT_FOUND = 'The layout "%s" does not have an item "%s".';

    const CONTROLLER_SERVICE_ACTION_FORM_ARGUMENT_MISSING = 'The method `%s::%s()` must have a parameter `$%s`. Note that you can also change the parameter `name` of the form view helper.';

    const SLOT_NOT_FOUND = 'No slot "%s" was found.';

    /**
     * @param string $name
     * @param array  $list
     * @return self
     */
    final public static function conditionNotFound($name, array $list)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::CONDITION_NOT_FOUND,
            1472650209,
            [
                $name,
                implode('" ,"', array_keys($list))
            ]
        );

        return $exception;
    }

    /**
     * @param string $name
     * @return self
     */
    final public static function activationConditionNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::ACTIVATION_CONDITION_NOT_FOUND,
            1488482191,
            [$name]
        );

        return $exception;
    }

    /**
     * @param string $name
     * @return self
     */
    final public static function configurationFieldNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::CONFIGURATION_FIELD_NOT_FOUND,
            1489765133,
            [$name, Form::class]
        );

        return $exception;
    }

    /**
     * @param string $name
     * @return self
     */
    final public static function validationNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::VALIDATION_NOT_FOUND,
            1487672276,
            [$name, AbstractActivation::class]
        );

        return $exception;
    }

    /**
     * @param string $name
     * @return self
     */
    final public static function viewLayoutNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::VIEW_LAYOUT_NOT_FOUND,
            1489753952,
            [$name, View::class]
        );

        return $exception;
    }

    /**
     * @param string $name
     * @return self
     */
    final public static function viewLayoutItemNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::VIEW_LAYOUT_ITEM_NOT_FOUND,
            1489757511,
            [$name, LayoutGroup::class]
        );

        return $exception;
    }

    /**
     * @param string $name
     * @return self
     */
    final public static function viewClassNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::VIEW_CLASS_NOT_FOUND,
            1489754909,
            [$name, ViewClass::class]
        );

        return $exception;
    }

    /**
     * @param string $validationName
     * @param string $fieldName
     * @return self
     */
    final public static function ajaxControllerValidationNotFoundForField($validationName, $fieldName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::VALIDATION_NOT_FOUND_FOR_FIELD,
            1487672956,
            [$fieldName, $validationName]
        );

        return $exception;
    }

    /**
     * @param string     $fieldName
     * @param FormObject $formObject
     * @return self
     */
    final public static function ajaxControllerFieldNotFound($fieldName, FormObject $formObject)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_NOT_FOUND,
            1487671603,
            [$fieldName, $formObject->getName(), $formObject->getClassName()]
        );

        return $exception;
    }

    /**
     * @param string            $key
     * @param AbstractValidator $validator
     * @return self
     */
    final public static function errorKeyNotFoundForValidator($key, AbstractValidator $validator)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::ERROR_KEY_NOT_FOUND_FOR_VALIDATOR,
            1455272659,
            [$key, get_class($validator)]
        );

        return $exception;
    }

    /**
     * @param string     $fieldName
     * @param FormObject $formObject
     * @return self
     */
    final public static function equalsToFieldValidatorFieldNotFound($fieldName, FormObject $formObject)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_NOT_FOUND,
            1487947224,
            [$fieldName, $formObject->getName(), $formObject->getClassName()]
        );

        return $exception;
    }

    /**
     * @param string $fieldName
     * @return self
     */
    final public static function classViewHelperFieldNotFound($fieldName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::VIEW_HELPER_FIELD_NOT_FOUND,
            1467623761,
            [$fieldName, ClassViewHelper::class, FieldViewHelper::class]
        );

        return $exception;
    }

    /**
     * @param string $fieldName
     * @return self
     */
    final public static function formatMessageViewHelperFieldNotFound($fieldName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::VIEW_HELPER_FIELD_NOT_FOUND,
            1467624152,
            [$fieldName, FormatMessageViewHelper::class, FieldViewHelper::class]
        );

        return $exception;
    }

    /**
     * @param string $layoutName
     * @return self
     */
    final public static function fieldViewHelperLayoutNotFound($layoutName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_VIEW_HELPER_LAYOUT_NOT_FOUND,
            1465243586,
            [$layoutName]
        );

        return $exception;
    }

    /**
     * @param string $layoutName
     * @param string $itemName
     * @return self
     */
    final public static function fieldViewHelperLayoutItemNotFound($layoutName, $itemName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_VIEW_HELPER_LAYOUT_ITEM_NOT_FOUND,
            1485867803,
            [$layoutName, $itemName]
        );

        return $exception;
    }

    /**
     * @param string     $fieldName
     * @param FormObject $formObject
     * @return self
     */
    final public static function formatMessageViewHelperFieldNotFoundInForm($fieldName, FormObject $formObject)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_NOT_FOUND,
            1473084335,
            [$fieldName, $formObject->getName(), $formObject->getClassName()]
        );

        return $exception;
    }

    /**
     * @param string $controllerObjectName
     * @param string $actionName
     * @param string $formName
     * @return self
     */
    final public static function controllerServiceActionFormArgumentMissing($controllerObjectName, $actionName, $formName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::CONTROLLER_SERVICE_ACTION_FORM_ARGUMENT_MISSING,
            1457441846,
            [$controllerObjectName, $actionName . 'Action', $formName]
        );

        return $exception;
    }

    /**
     * @param string $name
     * @return self
     */
    final public static function slotClosureSlotNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::SLOT_NOT_FOUND,
            1488988452,
            [$name]
        );

        return $exception;
    }

    /**
     * @param string $name
     * @return self
     */
    final public static function slotArgumentsSlotNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::SLOT_NOT_FOUND,
            1489497046,
            [$name]
        );

        return $exception;
    }
}
