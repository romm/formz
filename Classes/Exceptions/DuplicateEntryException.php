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

use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Form\FormObject;

class DuplicateEntryException extends FormzException
{
    const DUPLICATED_FORM_CONTEXT = 'You can not use a form view helper inside another one.';

    const FORM_WAS_ALREADY_REGISTERED = 'The form "%s" of class "%s" was already registered. You can only register a form once. Check the function `%s::hasForm()`.';

    /**
     * @return self
     */
    final public static function duplicatedFormContext()
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(self::DUPLICATED_FORM_CONTEXT, 1465242575);

        return $exception;
    }

    /**
     * @param FormObject $form
     * @return self
     */
    final public static function formWasAlreadyRegistered(FormObject $form)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FORM_WAS_ALREADY_REGISTERED,
            1477255145,
            [$form->getName(), $form->getClassName(), Configuration::class]
        );

        return $exception;
    }
}
