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

use Romm\Formz\Middleware\Element\AbstractMiddleware;
use Romm\Formz\Middleware\Signal\MiddlewareSignalInterface;

class SignalNotFoundException extends FormzException
{
    const SIGNAL_NOT_FOUND = 'The middleware "%s" is not bound to any signal. Make sure that this middleware implements one (and one only) signal interface based on "%s".';

    /**
     * @code 1490793544
     *
     * @param AbstractMiddleware $middleware
     * @return self
     */
    final public static function signalNotFoundInMiddleware(AbstractMiddleware $middleware)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::SIGNAL_NOT_FOUND,
            [get_class($middleware), MiddlewareSignalInterface::class]
        );

        return $exception;
    }
}
