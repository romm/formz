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

namespace Romm\Formz\Middleware;

use Romm\Formz\Middleware\Option\AbstractOptionDefinition;

interface MiddlewareComponentInterface extends MiddlewareInterface
{
    const PRIORITY_INJECT_FORM = 1000;

    /**
     * @param AbstractOptionDefinition $options
     */
    public function __construct(AbstractOptionDefinition $options);

    /**
     * @return void
     */
    public function initialize();

    /**
     * @return AbstractOptionDefinition
     */
    public function getOptions();

    /**
     * Must return a positive/negative integer priority. Considering two
     * middlewares, the one with the higher priority will be executed first.
     *
     * @return int
     */
    public function getPriority();

    /**
     * Returns the name of the signal on which this middleware is bound.
     *
     * @return string
     */
    public function getBoundSignalName();
}
