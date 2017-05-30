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

namespace Romm\Formz\Middleware\Item\Persistence;

use Romm\Formz\Middleware\Item\DefaultMiddleware;
use Romm\Formz\Middleware\Processor\PresetMiddlewareInterface;

/**
 * This middleware will check if the form was submitted, and inject it in every
 * persistence service bound to the form object.
 */
class PersistenceInjectionMiddleware extends DefaultMiddleware implements PresetMiddlewareInterface
{
    /**
     * @var int
     */
    protected $priority = self::PRIORITY_PERSISTENCE_INJECTION;

    /**
     * @see PersistenceInjectionMiddleware
     */
    public function process()
    {
        if ($this->getFormObject()->formWasSubmitted()) {
            $this->getFormObject()->getPersistenceManager()->save();
        }
    }
}
