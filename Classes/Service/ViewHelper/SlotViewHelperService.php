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

namespace Romm\Formz\Service\ViewHelper;

use Closure;
use Romm\Formz\Exceptions\EntryNotFoundException;
use TYPO3\CMS\Core\SingletonInterface;

class SlotViewHelperService implements SingletonInterface
{
    /**
     * Contains the closures which will render the registered slots. The keys
     * of this array are the names of the slots.
     *
     * @var Closure[]
     */
    private $closures = [];

    /**
     * @var array[]
     */
    private $arguments = [];

    /**
     * Adds a closure - which will render the slot with the given name - to the
     * private storage in this class.
     *
     * @param string  $name
     * @param Closure $closure
     * @param array   $arguments
     */
    public function addSlot($name, Closure $closure, array $arguments)
    {
        $this->closures[$name] = $closure;
        $this->arguments[$name] = $arguments;
    }

    /**
     * Returns the closure which will render the slot with the given name.
     *
     * @param string $name
     * @return Closure
     * @throws EntryNotFoundException
     */
    public function getSlotClosure($name)
    {
        if (false === $this->hasSlot($name)) {
            throw EntryNotFoundException::slotClosureSlotNotFound($name);
        }

        return $this->closures[$name];
    }

    /**
     * Returns the closure which will render the slot with the given name.
     *
     * @param string $name
     * @return array
     * @throws EntryNotFoundException
     */
    public function getSlotArguments($name)
    {
        if (false === $this->hasSlot($name)) {
            throw EntryNotFoundException::slotArgumentsSlotNotFound($name);
        }

        return $this->arguments[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasSlot($name)
    {
        return true === isset($this->closures[$name]);
    }

    /**
     * Resets the list of closures.
     */
    public function resetState()
    {
        $this->closures = [];
        $this->arguments = [];
    }
}
