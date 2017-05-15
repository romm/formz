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

namespace Romm\Formz\Configuration;

class ConfigurationState
{
    /**
     * @var bool
     */
    protected $isFrozen = false;

    /**
     * @return bool
     */
    public function isFrozen()
    {
        return $this->isFrozen;
    }

    /**
     * Marks the configuration as frozen, meaning recursive properties cannot be
     * edited anymore.
     *
     * @internal
     */
    public function markAsFrozen()
    {
        $this->isFrozen = true;
    }
}
