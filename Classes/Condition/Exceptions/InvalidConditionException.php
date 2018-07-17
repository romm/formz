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

namespace Romm\Formz\Condition\Exceptions;

use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Configuration\Form\Field\Validation\Validation;
use Romm\Formz\Exceptions\FormzException;

class InvalidConditionException extends FormzException
{
    const INVALID_FIELD_CONDITION_CONFIGURATION = 'Invalid configuration for the condition "%s", used on the field "%s" of the form "%s"; error message is « %s » (code: %s).';

    const INVALID_VALIDATION_CONDITION_CONFIGURATION = 'Invalid configuration for the condition "%s", used on the validation "%s" of the field "%s" of the form "%s"; error message is « %s » (code: %s).';

    const FIELD_DOES_NOT_EXIST = 'The field "%s" does not exist.';

    const VALIDATION_DOES_NOT_EXIST_FOR_FIELD = 'The validation "%s" does not exist for the field "%s".';

    /**
     * @code 1488653398
     *
     * @param string     $conditionName
     * @param Field      $field
     * @param string     $formClassName
     * @param \Exception $exception
     * @return InvalidConditionException
     */
    final public static function invalidFieldConditionConfiguration($conditionName, Field $field, $formClassName, \Exception $exception)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::INVALID_FIELD_CONDITION_CONFIGURATION,
            [
                $conditionName,
                $field->getName(),
                $formClassName,
                $exception->getMessage(),
                $exception->getCode()
            ]
        );

        return $exception;
    }

    /**
     * @code 1488653713
     *
     * @param string     $conditionName
     * @param Validation $validation
     * @param string     $formClassName
     * @param \Exception $exception
     * @return InvalidConditionException
     */
    final public static function invalidValidationConditionConfiguration($conditionName, Validation $validation, $formClassName, \Exception $exception)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::INVALID_VALIDATION_CONDITION_CONFIGURATION,
            [
                $conditionName,
                $validation->getName(),
                $validation->getParentField()->getName(),
                $formClassName,
                $exception->getMessage(),
                $exception->getCode()
            ]
        );

        return $exception;
    }

    /**
     * @code 1488192037
     *
     * @param string $fieldName
     * @return InvalidConditionException
     */
    final public static function conditionFieldHasErrorFieldNotFound($fieldName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_DOES_NOT_EXIST,
            [$fieldName]
        );

        return $exception;
    }

    /**
     * @code 1488192055
     *
     * @param string $validationName
     * @param string $fieldName
     * @return InvalidConditionException
     */
    final public static function conditionFieldHasErrorValidationNotFound($validationName, $fieldName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::VALIDATION_DOES_NOT_EXIST_FOR_FIELD,
            [$validationName, $fieldName]
        );

        return $exception;
    }

    /**
     * @code 1488192031
     *
     * @param string $fieldName
     * @return InvalidConditionException
     */
    final public static function conditionFieldHasValueFieldNotFound($fieldName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_DOES_NOT_EXIST,
            [$fieldName]
        );

        return $exception;
    }

    /**
     * @code 1488191994
     *
     * @param string $fieldName
     * @return InvalidConditionException
     */
    final public static function conditionFieldIsEmptyFieldNotFound($fieldName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_DOES_NOT_EXIST,
            [$fieldName]
        );

        return $exception;
    }

    /**
     * @code 1518016343128
     *
     * @param string $fieldName
     * @return InvalidConditionException
     */
    final public static function conditionFieldIsFilledFieldNotFound($fieldName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_DOES_NOT_EXIST,
            [$fieldName]
        );

        return $exception;
    }

    /**
     * @code 1488183577
     *
     * @param string $fieldName
     * @return InvalidConditionException
     */
    final public static function conditionFieldIsValidFieldNotFound($fieldName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_DOES_NOT_EXIST,
            [$fieldName]
        );

        return $exception;
    }

    /**
     * @code 1519909297
     *
     * @param string $fieldName
     * @return InvalidConditionException
     */
    final public static function conditionFieldCountValuesFieldNotFound($fieldName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_DOES_NOT_EXIST,
            [$fieldName]
        );

        return $exception;
    }
}
