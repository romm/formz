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

trait FormzMessageTrait
{
    /**
     * @var string
     */
    protected $validationName;

    /**
     * @var string
     */
    protected $messageKey;

    /**
     * @param string $validationName
     * @param string $messageKey
     */
    protected function injectValidationData($validationName, $messageKey)
    {
        $validationName = ($validationName) ?: 'unknown';
        $messageKey = ($messageKey) ?: 'unknown';

        $this->setValidationName($validationName);
        $this->setMessageKey($messageKey);
    }

    /**
     * @return string
     */
    public function getValidationName()
    {
        return $this->validationName;
    }

    /**
     * @param string $name
     */
    public function setValidationName($name)
    {
        $this->validationName = $name;
    }

    /**
     * @return string
     */
    public function getMessageKey()
    {
        return $this->messageKey;
    }

    /**
     * @param string $key
     */
    public function setMessageKey($key)
    {
        $this->messageKey = $key;
    }
}
