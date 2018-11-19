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

class ClassNotFoundException extends FormzException
{
    const WRONG_ASSET_HANDLER_CLASS_NAME = 'Trying to get an asset handler with a wrong class name: "%s".';

    const WRONG_FORM_CLASS_NAME = 'Invalid form class name given: "%s".';

    const FORM_VIEW_HELPER_CLASS_NOT_FOUND = 'Invalid value for the form class name (current value: "%s"). You need to either fill the parameter `formClassName` in the view helper, or specify the type of the parameter `$%s` for the method "%s::%s()".';

    const BACKEND_CACHE_CLASS_NAME_NOT_FOUND = 'The cache class name given in configuration "config.tx_formz.settings.defaultBackendCache" was not found (current value: "%s")';

    const CONDITION_CLASS_NAME_NOT_FOUND = 'The class name for the condition "%s" was not found (given value: "%s").';

    /**
     * @param string $name
     * @param string $className
     * @return self
     */
    final public static function conditionClassNameNotFound($name, $className)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::CONDITION_CLASS_NAME_NOT_FOUND,
            1489602455,
            [$name, $className]
        );

        return $exception;
    }

    /**
     * @param string $className
     * @return self
     */
    final public static function wrongAssetHandlerClassName($className)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::WRONG_ASSET_HANDLER_CLASS_NAME,
            1477468381,
            [$className]
        );

        return $exception;
    }

    /**
     * @param string $className
     * @return self
     */
    final public static function wrongFormClassName($className)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::WRONG_FORM_CLASS_NAME,
            1467191011,
            [$className]
        );

        return $exception;
    }

    /**
     * @param string $formClassName
     * @param string $formName
     * @param string $controller
     * @param string $action
     * @return self
     */
    final public static function formViewHelperClassNotFound($formClassName, $formName, $controller, $action)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FORM_VIEW_HELPER_CLASS_NOT_FOUND,
            1457442014,
            [$formClassName, $formName, $controller, $action . 'Action']
        );

        return $exception;
    }

    /**
     * @param string $className
     * @return self
     */
    final public static function backendCacheClassNameNotFound($className)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::BACKEND_CACHE_CLASS_NAME_NOT_FOUND,
            1488475103,
            [$className]
        );

        return $exception;
    }

    /**
     * @param string $className
     * @return self
     */
    final public static function ajaxControllerFormClassNameNotFound($className)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::WRONG_FORM_CLASS_NAME,
            1490179346,
            [$className]
        );

        return $exception;
    }
}
