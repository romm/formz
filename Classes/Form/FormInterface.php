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

namespace Romm\Formz\Form;

/**
 * This interface must be implemented by every form model which will use Formz
 * features.
 *
 * Please note that you can (should) use the `FormTrait` trait to implement all
 * the functions required by this interface.
 *
 * Note: If you want Formz to ignore a property of your form, use the annotation
 * `@formz-ignore` on it.
 */
interface FormInterface
{

    /**
     * @param string $key
     * @return array
     */
    public function getValidationData($key = null);

    /**
     * @param array $validationData
     * @internal
     */
    public function setValidationData(array $validationData);
}
