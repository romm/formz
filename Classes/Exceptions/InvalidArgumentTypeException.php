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

use Romm\Formz\AssetHandler\AbstractAssetHandler;
use Romm\Formz\Condition\Items\ConditionItemInterface;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\ViewHelpers\FieldViewHelper;
use Romm\Formz\ViewHelpers\FormatMessageViewHelper;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Error\Message;

class InvalidArgumentTypeException extends FormzException
{
    const WRONG_ASSET_HANDLER_TYPE = 'The asset handler object must be an instance of "%s", current type: "%s".';

    const WRONG_FORM_TYPE = 'The form class must be an instance of "%s", given value: "%s".';

    const VALIDATING_WRONG_FORM_TYPE = 'Trying to validate a form that does not implement the interface "%s". Given class: "%s"';

    const FIELD_VIEW_HELPER_INVALID_TYPE_NAME_ARGUMENT = 'The argument `name` of the view helper "%s" must be a string.';

    const FIELD_VIEW_HELPER_LAYOUT_NOT_STRING = 'The argument `layout` must be a string (%s given).';

    const FORMAT_MESSAGE_VIEW_HELPER_MESSAGE_INVALID_TYPE = 'The argument `message` for the view helper "%s" must be an instance of "%s" (`%s` given).';

    const CONDITION_NAME_NOT_STRING = 'The name of the condition must be a correct string (given type: "%s").';

    const CONDITION_CLASS_NAME_NOT_VALID = 'The condition class must implement "%s" (given class is "%s").';

    const PERSISTENCE_REPOSITORY_WRONG_FORM_TYPE = 'The given form instance must be an instance of "%s", given type is "%s".';

    const FORM_ARGUMENT_NOT_ARRAY = 'The request argument for the form "%s" (class: "%s") was not an array (found type is "%s"); there must have been a manual overriding of the argument.';

    const DATA_ATTRIBUTE_NOT_FORMATTABLE = 'The field "%s" in the form "%s" cannot be formatted. It needs to either implement `__toString()` or you need to use the `@formz-ignore` annotation in the property docblock.';

    /**
     * @param string $className
     * @return self
     */
    final public static function wrongAssetHandlerType($className)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::WRONG_ASSET_HANDLER_TYPE,
            1477468571,
            [AbstractAssetHandler::class, $className]
        );

        return $exception;
    }

    /**
     * @param string $className
     * @return self
     */
    final public static function wrongFormType($className)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::WRONG_FORM_TYPE,
            1488664221,
            [FormInterface::class, $className]
        );

        return $exception;
    }

    /**
     * @param string $className
     * @return self
     */
    final public static function validatingWrongFormType($className)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::VALIDATING_WRONG_FORM_TYPE,
            1487865158,
            [FormInterface::class, $className]
        );

        return $exception;
    }

    /**
     * @return self
     */
    final public static function fieldViewHelperInvalidTypeNameArgument()
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_VIEW_HELPER_INVALID_TYPE_NAME_ARGUMENT,
            1465243479,
            [FieldViewHelper::class]
        );

        return $exception;
    }

    /**
     * @param mixed $value
     * @return self
     */
    final public static function invalidTypeNameArgumentFieldViewHelper($value)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_VIEW_HELPER_LAYOUT_NOT_STRING,
            1485786193,
            [gettype($value)]
        );

        return $exception;
    }

    /**
     * @param mixed $value
     * @return self
     */
    final public static function formatMessageViewHelperMessageInvalidType($value)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(self::FORMAT_MESSAGE_VIEW_HELPER_MESSAGE_INVALID_TYPE,
            1467021406,
            [
                FormatMessageViewHelper::class,
                Message::class,
                is_object($value) ? get_class($value) : gettype($value)
            ]
        );

        return $exception;
    }

    /**
     * @param string $name
     * @return self
     */
    final public static function conditionNameNotString($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::CONDITION_NAME_NOT_STRING,
            1466588489,
            [gettype($name)]
        );

        return $exception;
    }

    /**
     * @param string $className
     * @return self
     */
    final public static function conditionClassNameNotValid($className)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::CONDITION_CLASS_NAME_NOT_VALID,
            1466588495,
            [ConditionItemInterface::class, $className]
        );

        return $exception;
    }

    /**
     * @param string $className
     * @return self
     */
    final public static function ajaxControllerWrongFormType($className)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::WRONG_FORM_TYPE,
            1490179427,
            [FormInterface::class, $className]
        );

        return $exception;
    }

    /**
     * @param FormInterface $form
     * @return self
     */
    final public static function persistenceRepositoryWrongFormType(FormInterface $form)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::PERSISTENCE_REPOSITORY_WRONG_FORM_TYPE,
            1491294921,
            [DomainObjectInterface::class, get_class($form)]
        );

        return $exception;
    }

    /**
     * @param FormObject $formObject
     * @param mixed      $formData
     * @return self
     */
    final public static function formArgumentNotArray(FormObject $formObject, $formData)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FORM_ARGUMENT_NOT_ARRAY,
            1492612944,
            [$formObject->getName(), $formObject->getClassName(), gettype($formData)]
        );

        return $exception;
    }

    /**
     * @param FormInterface $form
     * @param $fieldName
     * @param $value
     * @return InvalidArgumentTypeException
     */
    final public static function dataAttributeValueNotFormattable(FormInterface $form, $fieldName, $value)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::DATA_ATTRIBUTE_NOT_FORMATTABLE,
            1509724328,
            [
                $fieldName,
                get_class($form),
                $value,
            ]
        );

        return $exception;
    }
}
