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

class MissingArgumentException extends FormzException
{
    const ARGUMENT_MISSING = 'The argument "%s" was not found in the request.';

    /**
     * @code 1490179179
     *
     * @return MissingArgumentException
     */
    final public static function ajaxControllerNameArgumentNotSet()
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::ARGUMENT_MISSING,
            ['name']
        );

        return $exception;
    }

    /**
     * @code 1490179250
     *
     * @return MissingArgumentException
     */
    final public static function ajaxControllerClassNameArgumentNotSet()
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::ARGUMENT_MISSING,
            ['className']
        );

        return $exception;
    }
}
