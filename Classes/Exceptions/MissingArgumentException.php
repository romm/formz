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
    const REQUEST_ARGUMENTS_MISSING = 'One or more arguments are missing in the request: "%s".';

    /**
     * @code 1487673983
     *
     * @param array $missingArguments
     * @return MissingArgumentException
     */
    final public static function ajaxControllerMissingArguments(array $missingArguments)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::REQUEST_ARGUMENTS_MISSING,
            [implode('", "', $missingArguments)]
        );

        return $exception;
    }
}
