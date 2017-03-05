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

use Romm\Formz\Form\FormInterface;

class InvalidOptionValueException extends FormzException
{
    const WRONG_FORM_TYPE = 'The form class must be an instance of "%s", given value: "%s".';

    /**
     * @code 1457442462
     *
     * @param string $name
     * @return self
     */
    final public static function formViewHelperWrongFormType($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::WRONG_FORM_TYPE,
            [FormInterface::class, $name]
        );

        return $exception;
    }
}
