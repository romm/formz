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

class UnregisteredConfigurationException extends FormzException
{
    const CSS_CLASS_NAME_NOT_FOUND = 'The class "%s" is not valid: the class name "%s" was not found in the namespace "%s".';

    /**
     * @code 1467623662
     *
     * @param string $class
     * @param string $classNamespace
     * @param string $className
     * @return UnregisteredConfigurationException
     */
    final public static function cssClassNameNotFound($class, $classNamespace, $className)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::CSS_CLASS_NAME_NOT_FOUND,
            [$class, $className, $classNamespace]
        );

        return $exception;
    }
}
