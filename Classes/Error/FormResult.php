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

namespace Romm\Formz\Error;

use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Configuration\Form\Field\Validation\Validation;
use Romm\Formz\Service\Traits\StoreDataTrait;
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
     * @var array
     */
    protected $deactivatedFieldsValidation = [];

    /**
     * Flags the given field as deactivated.
     *
     * @param Field $field
     */
    public function deactivateField(Field $field)
    {
        $this->deactivatedFields[$field->getFieldName()] = true;
    }

    /**
     * Returns true if the given field is flagged as deactivated.
     *
     * @param Field $field
     * @return bool
     */
    public function fieldIsDeactivated(Field $field)
    {
        return array_key_exists($field->getFieldName(), $this->deactivatedFields);
    }

    /**
     * @param Validation $validation
     */
    public function deactivateValidation(Validation $validation)
    {
        $fieldName = $validation->getParentField()->getFieldName();

        if (false === isset($this->deactivatedFieldsValidation[$fieldName])) {
            $this->deactivatedFieldsValidation[$fieldName] = [];
        }

        $this->deactivatedFieldsValidation[$fieldName][$validation->getValidationName()] = true;
    }

    /**
     * @param Validation $validation
     * @return bool
     */
    public function validationIsDeactivated(Validation $validation)
    {
        $fieldName = $validation->getParentField()->getFieldName();

        return array_key_exists($fieldName, $this->deactivatedFieldsValidation)
            && array_key_exists($validation->getValidationName(), $this->deactivatedFieldsValidation[$fieldName]);
    }
}
