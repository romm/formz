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

use Romm\Formz\Form\FormInterface;

class InvalidArgumentValueException extends FormzException
{
    const FIELD_VIEW_HELPER_EMPTY_LAYOUT = 'The layout name cannot be empty, please fill with a value.';

    const FORM_NAME_EMPTY = 'The name of the form (type: "%s") can not be empty.';

    /**
     * @code 1485786285
     *
     * @return self
     */
    final public static function fieldViewHelperEmptyLayout()
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(self::FIELD_VIEW_HELPER_EMPTY_LAYOUT);

        return $exception;
    }

    /**
     * @code 1494515073
     *
     * @param FormInterface $form
     * @return self
     */
    final public static function formNameEmpty(FormInterface $form)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FORM_NAME_EMPTY,
            [get_class($form)]
        );

        return $exception;
    }
}
