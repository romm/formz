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

use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\Definition\Field\Validation\Validator;
use Romm\Formz\Service\Traits\StoreDataTrait;
use TYPO3\CMS\Extbase\Error\Result;

/**
 * Result used when validating a form instance; it provides more features than
 * the basic Extbase `Result` instance.
 */
class FormResult extends Result
{
    use StoreDataTrait;

    /**
     * @var Field[]
     */
    protected $deactivatedFields = [];

    /**
     * Contains fields that are not currently shown, they can be in a different
     * step or substep.
     *
     * @var Field[]
     */
    protected $fieldsOutOfScope = [];

    /**
     * @var Validator[][]
     */
    protected $deactivatedFieldsValidators = [];

    /**
     * @var array
     */
    protected $validatedFields = [];

    /**
     * Flags the given field as deactivated: its activation conditions did not
     * match.
     *
     * @param Field $field
     */
    public function deactivateField(Field $field)
    {
        $this->deactivatedFields[$field->getName()] = $field;
    }

    /**
     * Returns true if the given field is deactivated.
     *
     * @param Field $field
     * @return bool
     */
    public function fieldIsDeactivated(Field $field)
    {
        return array_key_exists($field->getName(), $this->deactivatedFields);
    }

    /**
     * @param Field $field
     */
    public function markFieldOutOfScope(Field $field)
    {
        $this->fieldsOutOfScope[$field->getName()] = $field;
    }

    /**
     * @param Field $field
     * @return bool
     */
    public function fieldIsOutOfScope(Field $field)
    {
        return array_key_exists($field->getName(), $this->fieldsOutOfScope);
    }

    /**
     * @return Field[]
     */
    public function getDeactivatedFields()
    {
        return $this->deactivatedFields;
    }

    /**
     * @param Validator $validator
     */
    public function deactivateValidator(Validator $validator)
    {
        $fieldName = $validator->getParentField()->getName();

        if (false === isset($this->deactivatedFieldsValidators[$fieldName])) {
            $this->deactivatedFieldsValidators[$fieldName] = [];
        }

        $this->deactivatedFieldsValidators[$fieldName][$validator->getName()] = $validator;
    }

    /**
     * @param Validator $validator
     * @return bool
     */
    public function validatorIsDeactivated(Validator $validator)
    {
        $fieldName = $validator->getParentField()->getName();

        return array_key_exists($fieldName, $this->deactivatedFieldsValidators)
            && array_key_exists($validator->getName(), $this->deactivatedFieldsValidators[$fieldName]);
    }

    /**
     * @return Validator[][]
     */
    public function getDeactivatedValidators()
    {
        return $this->deactivatedFieldsValidators;
    }

    /**
     * @param Field $field
     */
    public function markFieldAsValidated(Field $field)
    {
        $this->validatedFields[$field->getName()] = true;
    }

    /**
     * @return array
     */
    public function getValidatedFields()
    {
        return array_keys($this->validatedFields);
    }
}
