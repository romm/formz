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

namespace Romm\Formz\Exceptions;

use TYPO3\CMS\Extbase\Reflection\ClassReflection;

/**
 * A generic Formz exception.
 */
abstract class FormzException extends \Exception
{
    /**
     * Creates a new exception instance.
     *
     * The code will be fetched from the calling method's `@code` property.
     *
     * @param string $message
     * @param array  $arguments
     * @return FormzException
     */
    final protected static function getNewExceptionInstance($message, array $arguments = [])
    {
        $exceptionClassName = get_called_class();
        $methodName = ucfirst(debug_backtrace()[1]['function']);
        $reflection = new ClassReflection($exceptionClassName);
        $exceptionCode = end($reflection->getMethod($methodName)->getTagValues('code'));

        return new $exceptionClassName(
            vsprintf($message, $arguments),
            $exceptionCode
        );
    }
}
