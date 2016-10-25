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

namespace Romm\Formz\Error;

use Romm\Formz\Utility\Traits\StoreDataTrait;
use TYPO3\CMS\Extbase\Error\Result;

/**
 * Result used when validating a form instance; it provides more features than
 * the classic Extbase `Result` instance.
 */
class FormResult extends Result
{

    use StoreDataTrait;

    /**
     * @var array
     */
    protected $deactivatedFields = [];

    /**
     * Flags the given field as deactivated.
     *
     * @param string $fieldName
     */
    public function deactivateField($fieldName)
    {
        $this->deactivatedFields[$fieldName] = $fieldName;
    }

    /**
     * Returns true if the given field is flagged as deactivated.
     *
     * @param string $fieldName
     * @return bool
     */
    public function fieldIsDeactivated($fieldName)
    {
        return in_array($fieldName, $this->deactivatedFields);
    }
}
