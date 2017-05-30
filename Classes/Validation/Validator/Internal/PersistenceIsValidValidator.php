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

use Romm\Formz\Persistence\PersistenceInterface;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class PersistenceIsValidValidator extends AbstractValidator
{
    /**
     * @param string $persistence
     */
    public function isValid($persistence)
    {
        if (false === class_exists($persistence)) {
            $this->addError(
                'Class name given was not found: "%s".',
                1491224209,
                [$persistence]
            );
        } else {
            $interfaces = class_implements($persistence);

            if (false === in_array(PersistenceInterface::class, $interfaces)) {
                $this->addError(
                    'Class "%s" must implement "%s".',
                    1489070282,
                    [$persistence, PersistenceInterface::class]
                );
            }
        }
    }
}
