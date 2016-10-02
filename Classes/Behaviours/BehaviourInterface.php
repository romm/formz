<?php
/*
 * 2016 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Formz project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\Behaviours;

interface BehaviourInterface
{

    /**
     * The function where you can apply your own behaviour on a value.
     *
     * @param    mixed $value The value which will be modified.
     * @return    mixed            The modified value.
     */
    public function applyBehaviour($value);
}
