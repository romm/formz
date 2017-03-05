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

namespace Romm\Formz\Exceptions;

class InvalidConfigurationException extends FormzException
{
    const INVALID_FORM_CONFIGURATION = 'The form configuration contains errors.';

    const AJAX_VALIDATION_NOT_ACTIVATED = 'The validation "%s" of the field "%s" is not configured to work with Ajax. Please add the option `useAjax`.';

    /**
     * @code 1487671395
     *
     * @return InvalidConfigurationException
     */
    final public static function ajaxControllerInvalidFormConfiguration()
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(self::INVALID_FORM_CONFIGURATION);

        return $exception;
    }

    /**
     * @code 1487673434
     *
     * @param string $validationName
     * @param string $fieldName
     * @return InvalidConfigurationException
     */
    final public static function ajaxControllerAjaxValidationNotActivated($validationName, $fieldName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::AJAX_VALIDATION_NOT_ACTIVATED,
            [$validationName, $fieldName]
        );

        return $exception;
    }
}
