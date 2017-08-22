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

namespace Romm\Formz\Middleware\Signal\Element;

use Romm\Formz\Middleware\Argument\Arguments;
use Romm\Formz\Middleware\Argument\EmptyArguments;
use Romm\Formz\Middleware\Processor\MiddlewareProcessor;
use Romm\Formz\Middleware\Signal\Before;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SignalObject
{
    /**
     * @var MiddlewareProcessor
     */
    protected $processor;

    /**
     * @var string
     */
    protected $signal;

    /**
     * @var string
     */
    protected $type;

    /**
     @var Arguments
     */
    protected $arguments;

    /**
     * @param MiddlewareProcessor $processor
     * @param string              $signal
     * @param string              $type
     */
    public function __construct(MiddlewareProcessor $processor, $signal, $type)
    {
        $this->processor = $processor;
        $this->signal = $signal;
        $this->type = $type;
        $this->arguments = GeneralUtility::makeInstance(EmptyArguments::class);
    }

    /**
     * @param Arguments $arguments
     * @return $this
     */
    public function withArguments(Arguments $arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Will dispatch the configured signal to all middlewares bound to the
     * signal name.
     */
    public function dispatch()
    {
        foreach ($this->processor->getMiddlewaresBoundToSignal($this->signal) as $middleware) {
            if ($middleware instanceof $this->type) {
                $method = $this->type === Before::class
                    ? 'before'
                    : 'after';

                call_user_func([$middleware, $method], $this->arguments);
            }
        }
    }
}
