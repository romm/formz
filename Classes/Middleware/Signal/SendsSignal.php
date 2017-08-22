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

namespace Romm\Formz\Middleware\Signal;

use Romm\Formz\Middleware\Signal\Element\SignalObject;

/**
 * This interface must be implemented by a middleware if it needs to send
 * signals to other middlewares.
 */
interface SendsSignal
{
    /**
     * This method should be called before the middleware starts processing.
     *
     * It will dispatch the before-signal to middlewares that are bound to the
     * given signal.
     *
     * @param string $signal
     * @return SignalObject
     */
    public function beforeSignal($signal = null);

    /**
     * This method should be called when the middleware has finished processing.
     *
     * It will dispatch the after-signal to middlewares that are bound to the
     * given signal.
     *
     * @param string $signal
     * @return SignalObject
     */
    public function afterSignal($signal = null);

    /**
     * Must return an array containing names of the signals interfaces that can
     * be dispatched by this middleware.
     *
     * Each signal interface must extend `MiddlewareSignal`.
     *
     * @return array
     */
    public function getAllowedSignals();
}
