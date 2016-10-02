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

use TYPO3\CMS\Extbase\Error\Result;

/**
 * This class is exclusively for user function validators and Ajax calls.
 *
 * When using `UserFunctionValidator`, the result of the given user function
 * must be an instance of this class.
 *
 * In this class, you can handle two features:
 *
 * - Arbitrary data:
 *   You can add any custom value you want, which is stored and can then be
 *   fetched later in the process.
 *
 * - Error message key:
 *   This extension gives the possibility to handle different messages in a
 *   single validator instance. For user function validators, it is possible to
 *   add any custom message with TypoScript. This is why you can use the
 *   function `setErrorMessageKey()` to indicate which error message you want to
 *   display.
 */
class UserFunctionResult extends Result
{

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var string
     */
    protected $errorMessageKey = 'default';

    /**
     * @param string|null $key
     * @return array
     */
    public function getData($key = null)
    {
        $result = $this->data;
        if (null !== $key && isset($this->data[$key])) {
            $result = $this->data[$key];
        }

        return $result;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function setDataValue($key, $value)
    {
        $this->data[(string)$key] = $value;
    }

    /**
     * @param string $key
     */
    public function setErrorMessageKey($key)
    {
        $this->errorMessageKey = (string)$key;
    }

    /**
     * @return string
     */
    public function getErrorMessageKey()
    {
        return $this->errorMessageKey;
    }
}
