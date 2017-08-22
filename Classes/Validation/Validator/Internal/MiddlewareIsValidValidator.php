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

namespace Romm\Formz\Validation\Validator\Internal;

use Romm\Formz\Domain\Middleware\Begin\BeginSignal;
use Romm\Formz\Domain\Middleware\End\EndSignal;
use Romm\Formz\Middleware\Element\MiddlewareInterface;
use Romm\Formz\Middleware\Processor\PresetMiddlewareInterface;
use Romm\Formz\Middleware\Signal\After;
use Romm\Formz\Middleware\Signal\Before;
use Romm\Formz\Middleware\Signal\MiddlewareSignalInterface;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class MiddlewareIsValidValidator extends AbstractValidator
{
    /**
     * @param string $middleware
     */
    public function isValid($middleware)
    {
        $this->checkImplementation($middleware);
    }

    /**
     * @param string $middleware
     */
    protected function checkImplementation($middleware)
    {
        if (false === class_exists($middleware)) {
            $this->addError(
                'Class name given was not found: "%s".',
                1489070202,
                [$middleware]
            );
        } else {
            $interfaces = class_implements($middleware);

            if (false === in_array(MiddlewareInterface::class, $interfaces)) {
                $this->addError(
                    'Class "%s" must implement "%s".',
                    1489070282,
                    [$middleware, MiddlewareInterface::class]
                );
            }

            if (in_array(PresetMiddlewareInterface::class, $interfaces)) {
                $this->addError(
                    'You can not register a FormZ preset middleware (%s), check the `presetMiddlewares` configuration.',
                    1490967642,
                    [$middleware]
                );
            }

            $signalsFound = [];
            foreach ($interfaces as $interface) {
                if (in_array(MiddlewareSignalInterface::class, class_implements($interface))) {
                    $signalsFound[] = $interface;
                }
            }

            if (empty($signalsFound)) {
                $this->addError(
                    'Class "%s" must implement one interface that extends "%s".',
                    1489074248,
                    [$middleware, MiddlewareSignalInterface::class]
                );
            } elseif (count($signalsFound) > 1) {
                $this->addError(
                    'Class "%s" must implement only one interface that extends "%s"; %s were found: "%s"',
                    1489074852,
                    [
                        $middleware,
                        MiddlewareSignalInterface::class,
                        count($signalsFound),
                        implode('", "', $signalsFound)
                    ]
                );
            }

            if (false === in_array(Before::class, $interfaces)
                && false === in_array(After::class, $interfaces)
            ) {
                $this->addError(
                    'Class "%s" must implement at least one of these interfaces: "%s", "%s".',
                    1489074986,
                    [
                        $middleware,
                        Before::class,
                        After::class
                    ]
                );
            }

            if (false === $this->result->hasErrors()) {
                if (in_array(BeginSignal::class, $interfaces)
                    && in_array(Before::class, $interfaces)
                ) {
                    $this->addError(
                        'Class "%s" implements interfaces "%s" and "%s", but the signal "before beginning" is (obviously) invalid. Please remove "%s" dependency.',
                        1489075185,
                        [
                            $middleware,
                            Before::class,
                            BeginSignal::class,
                            Before::class,
                        ]
                    );
                }

                if (in_array(EndSignal::class, $interfaces)
                    && in_array(After::class, $interfaces)
                ) {
                    $this->addError(
                        'Class "%s" implements interfaces "%s" and "%s", but the signal "after the end" is (obviously) invalid. Please remove "%s" dependency.',
                        1489075242,
                        [
                            $middleware,
                            After::class,
                            EndSignal::class,
                            After::class,
                        ]
                    );
                }
            }
        }
    }
}
