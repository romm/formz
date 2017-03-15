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
use Romm\Formz\Form\FormInterface;
use Romm\Formz\ViewHelpers\FieldViewHelper;
use Romm\Formz\ViewHelpers\FormatMessageViewHelper;
use TYPO3\CMS\Extbase\Error\Message;

class InvalidArgumentTypeException extends FormzException
{
    const WRONG_ASSET_HANDLER_TYPE = 'The asset handler object must be an instance of "%s", current type: "%s".';

    const WRONG_FORM_TYPE = 'The form class must be an instance of "%s", given value: "%s".';

    const VALIDATING_WRONG_FORM_TYPE = 'Trying to validate a form that does not implement the interface "%s". Given class: "%s"';

    const FIELD_VIEW_HELPER_INVALID_TYPE_NAME_ARGUMENT = 'The argument `name` of the view helper "%s" must be a string.';

    const FIELD_VIEW_HELPER_LAYOUT_NOT_STRING = 'The argument `layout` must be a string (%s given).';

    const FORMAT_MESSAGE_VIEW_HELPER_MESSAGE_INVALID_TYPE = 'The argument `message` for the view helper "%s" must be an instance of "%s" (`%s` given).';

    /**
     * @code 1477468571
     *
     * @param string $className
     * @return self
     */
    final public static function wrongAssetHandlerType($className)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::WRONG_ASSET_HANDLER_TYPE,
            [AbstractAssetHandler::class, $className]
        );

        return $exception;
    }

    /**
     * @code 1488664221
     *
     * @param string $className
     * @return self
     */
    final public static function wrongFormType($className)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::WRONG_FORM_TYPE,
            [FormInterface::class, $className]
        );

        return $exception;
    }

    /**
     * @code 1487865158
     *
     * @param string $className
     * @return self
     */
    final public static function validatingWrongFormType($className)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::VALIDATING_WRONG_FORM_TYPE,
            [FormInterface::class, $className]
        );

        return $exception;
    }

    /**
     * @code 1465243479
     *
     * @return self
     */
    final public static function fieldViewHelperInvalidTypeNameArgument()
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_VIEW_HELPER_INVALID_TYPE_NAME_ARGUMENT,
            [FieldViewHelper::class]
        );

        return $exception;
    }

    /**
     * @code 1485786193
     *
     * @param mixed $value
     * @return InvalidArgumentTypeException
     */
    final public static function invalidTypeNameArgumentFieldViewHelper($value)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_VIEW_HELPER_LAYOUT_NOT_STRING,
            [gettype($value)]
        );

        return $exception;
    }

    /**
     * @code 1467021406
     *
     * @param mixed $value
     * @return InvalidArgumentTypeException
     */
    final public static function formatMessageViewHelperMessageInvalidType($value)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(self::FORMAT_MESSAGE_VIEW_HELPER_MESSAGE_INVALID_TYPE,
            [
                FormatMessageViewHelper::class,
                Message::class,
                is_object($value) ? get_class($value) : gettype($value)
            ]
        );

        return $exception;
    }
}
