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

use Romm\Formz\Form\FormObject;

class PropertyNotAccessibleException extends FormzException
{
    const FIELD_NOT_ACCESSIBLE_IN_FORM = 'The form "%s" does not have an accessible property "%s". Please be sure this property exists, and it has a proper getter to access its value.';

    /**
     * @param FormObject $formObject
     * @param string     $fieldName
     * @return self
     */
    final public static function fieldViewHelperFieldNotAccessibleInForm(FormObject $formObject, $fieldName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_NOT_ACCESSIBLE_IN_FORM,
            1465243619,
            [$formObject->getClassName(), $fieldName]
        );

        return $exception;
    }
}
