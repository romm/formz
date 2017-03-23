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

class InvalidEntryException extends FormzException
{
    const INVALID_CSS_CLASS_NAMESPACE = 'The class "%s" is not valid: the namespace of the error must be one of the following: %s.';

    /**
     * @code 1467623504
     *
     * @param string $className
     * @param array  $acceptedClassesNameSpace
     * @return InvalidEntryException
     */
    final public static function invalidCssClassNamespace($className, array $acceptedClassesNameSpace)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::INVALID_CSS_CLASS_NAMESPACE,
            [$className, implode(', ', $acceptedClassesNameSpace)]
        );

        return $exception;
    }
}
