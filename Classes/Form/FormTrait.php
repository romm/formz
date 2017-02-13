<?php
/*
 * 2017 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Formz project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\Form;

/**
 * This trait should be used by default to implement all the functions required
 * by the interface `FormInterface`.
 *
 * This is not advised to overrides the function provided by this trait, unless
 * you know what you are doing.
 */
trait FormTrait
{

    /**
     * Contains the optional data returned from the validators of each field.
     *
     * @var array
     */
    protected $validationData = [];

    /**
     * @param string $key
     * @return array
     */
    public function getValidationData($key = null)
    {
        $result = $this->validationData;

        if (null !== $key) {
            $result = (isset($this->validationData[$key]))
                ? $result = $this->validationData[$key]
                : null;
        }

        return $result;
    }

    /**
     * @param array $validationData
     * @internal
     */
    public function setValidationData(array $validationData)
    {
        $this->validationData = $validationData;
    }
}
