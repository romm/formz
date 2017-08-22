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

use Romm\Formz\Middleware\Argument\Arguments;

interface After
{
    /**
     * This method is called after the dispatching of the signal on which the
     * middleware is bound. The main logic of the middleware will be implemented
     * here.
     *
     * Arguments may be passed to the method, depending on the signal.
     *
     * @param Arguments $arguments
     * @return void
     */
    public function after(Arguments $arguments);
}
