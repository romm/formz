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

class InvalidArgumentValueException extends FormzException
{
    const FIELD_VIEW_HELPER_EMPTY_LAYOUT = 'The layout name cannot be empty, please fill with a value.';

    /**
     * @code 1485786285
     *
     * @return InvalidArgumentValueException
     */
    final public static function fieldViewHelperEmptyLayout()
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(self::FIELD_VIEW_HELPER_EMPTY_LAYOUT);

        return $exception;
    }
}
