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

namespace Romm\Formz\Service;

use Romm\Formz\Service\Traits\FacadeInstanceTrait;
use TYPO3\CMS\Core\SingletonInterface;

class HashService implements SingletonInterface
{
    use FacadeInstanceTrait;

    /**
     * @param string $value
     * @return string
     */
    public function getHash($value)
    {
        return hash('sha256', $value);
    }
}
