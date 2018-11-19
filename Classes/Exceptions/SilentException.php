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

namespace Romm\Formz\Exceptions;

use Romm\ConfigurationObject\Exceptions\SilentExceptionInterface;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\Definition\Field\Validation\Validator;

class SilentException extends FormzException implements SilentExceptionInterface
{
    const FIELD_HAS_NO_ACTIVATION = 'The field "%s" does not have activation. Please use the function `%s::hasActivation()` before.';

    const VALIDATOR_HAS_NO_ACTIVATION = 'The validator "%s" does not have activation. Please use the function `%s::hasActivation()` before.';

    /**
     * @param Field $field
     * @return self
     */
    final public static function fieldHasNoActivation(Field $field)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_HAS_NO_ACTIVATION,
            1494685913,
            [$field->getName(), Field::class]
        );

        return $exception;
    }

    /**
     * @param Validator $validator
     * @return self
     */
    final public static function validatorHasNoActivation(Validator $validator)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::VALIDATOR_HAS_NO_ACTIVATION,
            1494690671,
            [$validator->getName(), Validator::class]
        );

        return $exception;
    }
}
