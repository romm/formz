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

namespace Romm\Formz\Middleware\Request;

use Romm\Formz\Middleware\Request\Exception\ForwardException;

class Forward extends Dispatcher
{
    /**
     * @throws ForwardException
     */
    public function dispatch()
    {
        $this->request->setControllerActionName($this->action);
        $this->request->setControllerName($this->controller);
        $this->request->setControllerExtensionName($this->extension);
        $this->request->setArguments($this->arguments);

        throw new ForwardException;
    }
}
