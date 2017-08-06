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

namespace Romm\Formz\Middleware\Item;

use Romm\Formz\Middleware\Argument\Arguments;
use Romm\Formz\Middleware\Item\Begin\BeginSignal;
use Romm\Formz\Middleware\Signal\After;

/**
 * Middleware abstraction that can be extended by custom middleware.
 *
 * Note that a middleware extending this class will be called at the beginning
 * of the middleware processing.
 *
 * If you need the middleware to be called later, you can:
 * - @see \Romm\Formz\Middleware\Item\DefaultMiddleware (called at the end)
 * - @see \Romm\Formz\Middleware\Item\AbstractMiddleware
 */
abstract class OnBeginMiddleware extends AbstractMiddleware implements After, BeginSignal
{
    /**
     * @var Arguments
     */
    private $arguments;

    /**
     * @return void
     */
    abstract protected function process();

    /**
     * We override this function to manually call the `process` function.
     *
     * @param Arguments $arguments
     */
    final public function after(Arguments $arguments)
    {
        $this->arguments = $arguments;

        $this->process();
    }

    /**
     * @return Arguments
     */
    final protected function getArguments()
    {
        return $this->arguments;
    }
}
