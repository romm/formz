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

use Romm\Formz\Middleware\Signal\SendsMiddlewareSignal;

class MissingArgumentException extends FormzException
{
    const ARGUMENT_MISSING = 'The argument "%s" was not found in the request.';

    const CONDITION_CONSTRUCTOR_ARGUMENT_MISSING = 'Error while instantiating the condition "%s" of type "%s": a constructor argument is missing. Given arguments were: "%s".';

    const SIGNAL_NAME_MISSING = 'No signal has been given to the signal dispatcher, used in the middleware "%s". This is because this middleware can dispatch several signals (namely "%s"); so you must indicate which signal to dispatch.';

    /**
     * @code 1490179179
     *
     * @return self
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
     * @return self
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

    /**
     * @code 1494850270
     *
     * @param string $conditionName
     * @param string $conditionClassName
     * @param array  $arguments
     * @return self
     */
    final public static function conditionConstructorArgumentMissing($conditionName, $conditionClassName, array $arguments)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::CONDITION_CONSTRUCTOR_ARGUMENT_MISSING,
            [$conditionName, $conditionClassName, implode('", "', array_keys($arguments))]
        );

        return $exception;
    }

    /**
     * @code 1490793826
     *
     * @param SendsMiddlewareSignal $middleware
     * @return self
     */
    final public static function signalNameArgumentMissing(SendsMiddlewareSignal $middleware)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::SIGNAL_NAME_MISSING,
            [
                get_class($middleware),
                implode('", "', $middleware->getAllowedSignals())
            ]
        );

        return $exception;
    }
}
