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
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Form\FormObject\FormObjectStatic;

class DuplicateEntryException extends FormzException
{
    const DUPLICATED_FORM_CONTEXT = 'You can not use a form view helper inside another one.';

    const FORM_WAS_ALREADY_REGISTERED = 'The form "%s" was already registered. You can only register a form once. Check the function `%s::hasForm()`.';

    const FORM_INSTANCE_ALREADY_ADDED = 'The form instance was already added for the form object of class "%s". You cannot add it twice.';

    const FORM_OBJECT_INSTANCE_ALREADY_REGISTERED = 'The form instance of type "%s" (name "%s") was already registered in the form object factory.';

    /**
     * @code 1465242575
     *
     * @return self
     */
    final public static function duplicatedFormContext()
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(self::DUPLICATED_FORM_CONTEXT);

        return $exception;
    }

    /**
     * @code 1477255145
     *
     * @param FormObjectStatic $form
     * @return self
     */
    final public static function formWasAlreadyRegistered(FormObjectStatic $form)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FORM_WAS_ALREADY_REGISTERED,
            [$form->getClassName(), Configuration::class]
        );

        return $exception;
    }

    /**
     * @code 1491898212
     *
     * @param FormObject $formObject
     * @return self
     */
    final public static function formInstanceAlreadyAdded(FormObject $formObject)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FORM_INSTANCE_ALREADY_ADDED,
            [$formObject->getClassName()]
        );

        return $exception;
    }

    /**
     * @code 1494515318
     *
     * @param FormInterface $form
     * @param string        $name
     * @return self
     */
    final public static function formObjectInstanceAlreadyRegistered(FormInterface $form, $name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FORM_OBJECT_INSTANCE_ALREADY_REGISTERED,
            [get_class($form), $name]
        );

        return $exception;
    }
}
