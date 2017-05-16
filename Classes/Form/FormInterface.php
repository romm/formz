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

namespace Romm\Formz\Form;

/**
 * This interface must be implemented by every form model which will use FormZ
 * features.
 *
 * Please note that you can (should) use the `FormTrait` trait to implement all
 * the functions required by this interface.
 *
 * Note: If you want FormZ to ignore a property of your form, use the annotation
 * `@formz-ignore` on it.
 */
interface FormInterface
{
    /**
     * @deprecated This method is deprecated and will be deleted in FormZ v2.
     *
     * @param string $key
     * @return array
     */
    public function getValidationData($key = null);

    /**
     * @deprecated This method is deprecated and will be deleted in FormZ v2.
     *
     * @param array $validationData
     * @internal
     */
    public function setValidationData(array $validationData);
}
