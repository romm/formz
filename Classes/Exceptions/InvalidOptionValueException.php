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

use Romm\Formz\Form\FormInterface;
use Romm\Formz\ViewHelpers\FormIdentifierHashViewHelper;
use Romm\Formz\ViewHelpers\FormViewHelper;
use TYPO3\CMS\Core\Cache\Backend\AbstractBackend;

class InvalidOptionValueException extends FormzException
{
    const WRONG_FORM_TYPE = 'The form class must be an instance of "%s", given value: "%s".';

    const WRONG_FORM_VALUE_TYPE = 'The form given in the argument `%s` of the view helper "%s" must be an instance of "%s", given value is of type "%s".';

    const WRONG_FORM_VALUE_OBJECT_TYPE = 'The form given in the argument `%s` of the view helper "%s" must be an instance of "%s", given value is an instance of "%s".';

    const WRONG_FORM_VALUE_CLASS_NAME = 'The form given in the argument `%s` of the view helper "%s" must be an instance of "%s", given value is an instance of "%s".';

    const WRONG_BACKEND_CACHE_TYPE = 'The cache class name given in configuration "config.tx_formz.settings.defaultBackendCache" must inherit "%s" (current value: "%s")';

    /**
     * @param string $name
     * @return self
     */
    final public static function formViewHelperWrongFormType($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::WRONG_FORM_TYPE,
            1457442462,
            [FormInterface::class, $name]
        );

        return $exception;
    }

    /**
     * @param mixed $value
     * @return self
     */
    final public static function formViewHelperWrongFormValueType($value)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::WRONG_FORM_VALUE_TYPE,
            1490713939,
            [
                'object',
                FormViewHelper::class,
                FormInterface::class,
                gettype($value)
            ]
        );

        return $exception;
    }

    /**
     * @param object $value
     * @return self
     */
    final public static function formViewHelperWrongFormValueObjectType($value)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::WRONG_FORM_VALUE_OBJECT_TYPE,
            1490714346,
            [
                'object',
                FormViewHelper::class,
                FormInterface::class,
                get_class($value)
            ]
        );

        return $exception;
    }

    /**
     * @param string $className
     * @param object $value
     * @return self
     */
    final public static function formViewHelperWrongFormValueClassName($className, $value)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::WRONG_FORM_VALUE_CLASS_NAME,
            1490714534,
            [
                'object',
                FormViewHelper::class,
                $className,
                get_class($value)
            ]
        );

        return $exception;
    }

    /**
     * @param string $className
     * @return self
     */
    final public static function wrongBackendCacheType($className)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::WRONG_BACKEND_CACHE_TYPE,
            1459251263,
            [AbstractBackend::class, $className]
        );

        return $exception;
    }

    /**
     * @param mixed $value
     * @return self
     */
    final public static function formIdentifierViewHelperWrongFormValueType($value)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::WRONG_FORM_VALUE_TYPE,
            1490959351,
            [
                'form',
                FormIdentifierHashViewHelper::class,
                FormInterface::class,
                gettype($value)
            ]
        );

        return $exception;
    }

    /**
     * @param object $value
     * @return self
     */
    final public static function formIdentifierViewHelperWrongFormValueObjectType($value)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::WRONG_FORM_VALUE_OBJECT_TYPE,
            1490959375,
            [
                'form',
                FormIdentifierHashViewHelper::class,
                FormInterface::class,
                get_class($value)
            ]
        );

        return $exception;
    }
}
