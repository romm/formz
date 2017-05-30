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

namespace Romm\Formz\Middleware\Argument;

use Romm\Formz\Exceptions\EntryNotFoundException;

/**
 * Abstract class that must be extended by classes used to send arguments to a
 * middleware.
 */
abstract class Arguments
{
    /**
     * @var Argument[]
     */
    protected $arguments = [];

    /**
     * @param string $name
     * @param mixed  $value
     * @return $this
     */
    public function add($name, $value)
    {
        $this->arguments[$name] = new Argument($name, $value);

        return $this;
    }

    /**
     * @param string $name
     * @return Argument
     * @throws EntryNotFoundException
     */
    public function get($name)
    {
        if (false === $this->has($name)) {
            throw EntryNotFoundException::argumentNotFound($name);
        }

        return $this->arguments[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return true === isset($this->arguments[$name]);
    }
}
