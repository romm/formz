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
use TYPO3\CMS\Core\Cache\Backend\AbstractBackend;

class InvalidOptionValueException extends FormzException
{
    const WRONG_FORM_TYPE = 'The form class must be an instance of "%s", given value: "%s".';

    const WRONG_FORM_VALUE_TYPE = 'The form given in the argument `object` of `FormViewHelper` must be an instance of "%s", given value is of type "%s".';

    const WRONG_FORM_VALUE_OBJECT_TYPE = 'The form given in the argument `object` of `FormViewHelper` must be an instance of "%s", given value is an instance of "%s".';

    const WRONG_FORM_VALUE_CLASS_NAME = 'The form given in the argument `object` of `FormViewHelper` must be an instance of "%s", given value is an instance of "%s".';

    const WRONG_BACKEND_CACHE_TYPE = 'The cache class name given in configuration "config.tx_formz.settings.defaultBackendCache" must inherit "%s" (current value: "%s")';

    /**
     * @code 1457442462
     *
     * @param string $name
     * @return self
     */
    final public static function formViewHelperWrongFormType($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::WRONG_FORM_TYPE,
            [FormInterface::class, $name]
        );

        return $exception;
    }

    /**
     * @code 1490713939
     *
     * @param mixed $value
     * @return self
     */
    final public static function formViewHelperWrongFormValueType($value)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::WRONG_FORM_VALUE_TYPE,
            [FormInterface::class, gettype($value)]
        );

        return $exception;
    }

    /**
     * @code 1490714346
     *
     * @param object $value
     * @return self
     */
    final public static function formViewHelperWrongFormValueObjectType($value)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::WRONG_FORM_VALUE_OBJECT_TYPE,
            [FormInterface::class, get_class($value)]
        );

        return $exception;
    }

    /**
     * @code 1490714534
     *
     * @param string $className
     * @param object $value
     * @return self
     */
    final public static function formViewHelperWrongFormValueClassName($className, $value)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::WRONG_FORM_VALUE_CLASS_NAME,
            [$className, get_class($value)]
        );

        return $exception;
    }

    /**
     * @code 1459251263
     *
     * @param string $className
     * @return self
     */
    final public static function wrongBackendCacheType($className)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::WRONG_BACKEND_CACHE_TYPE,
            [AbstractBackend::class, $className]
        );

        return $exception;
    }
}
