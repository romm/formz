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

namespace Romm\Formz\Error;

interface FormzMessageInterface
{
    /**
     * @return string
     */
    public function getValidationName();

    /**
     * @param string $name
     */
    public function setValidationName($name);

    /**
     * @return string
     */
    public function getMessageKey();

    /**
     * @param string $key
     */
    public function setMessageKey($key);
}
