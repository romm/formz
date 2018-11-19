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

/**
 * A generic FormZ exception.
 */
abstract class FormzException extends \Exception
{
    /**
     * Creates a new exception instance.
     *
     * @param string $message
     * @param int $code
     * @param array $arguments
     * @return FormzException
     */
    final protected static function getNewExceptionInstance($message, $code, array $arguments = [])
    {
        $exceptionClassName = get_called_class();

        return new $exceptionClassName(
            vsprintf($message, $arguments),
            $code
        );
    }
}
