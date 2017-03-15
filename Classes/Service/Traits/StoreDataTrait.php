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

namespace Romm\Formz\Service\Traits;

use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * This trait can be used by any class which needs usage of data which must be
 * accessible by external classes.
 *
 * See description of the functions `setData()` and `getData()` for more
 * information.
 *
 * @internal This trait is for FormZ internal usage only: it may change at any moment, so do not use it in your own scripts!
 */
trait StoreDataTrait
{

    /**
     * @var array
     */
    protected $internalData = [];

    /**
     * Returns the asked data. If `$key` is null, the full data array is
     * returned. The argument `$key` can be a path to a value in the array, with
     * a dot (`.`) being the keys separator.
     *
     * Example: `$key = foo.bar` - the result will be the value stored in
     * `$data['foo']['bar']` (if it is found).
     *
     * @param string|null $key
     * @return array
     */
    public function getData($key = null)
    {
        $result = null;

        if (null === $key) {
            $result = $this->internalData;
        } else {
            if (false !== strpos($key, '.')) {
                if (ArrayUtility::isValidPath($this->internalData, $key, '.')) {
                    $result = ArrayUtility::getValueByPath($this->internalData, $key, '.');
                }
            } elseif (isset($this->internalData[$key])) {
                $result = $this->internalData[$key];
            }
        }

        return $result;
    }

    /**
     * Stores the given value at the given key. The argument `$key` can be a
     * path to a value in an array, with a dot (`.`) being the keys separator.
     *
     * Example: `$key = foo.bar` - the value will be stored in
     * `$data['foo']['bar']`
     *
     * @param string $key
     * @param mixed  $value
     */
    public function setData($key, $value)
    {
        if (false !== strpos($key, '.')) {
            $this->internalData = ArrayUtility::setValueByPath($this->internalData, $key, $value, '.');
        } else {
            $this->internalData[$key] = $value;
        }
    }
}
