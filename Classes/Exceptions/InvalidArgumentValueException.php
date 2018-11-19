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

use Romm\Formz\Middleware\Signal\SendsMiddlewareSignal;

use Romm\Formz\Form\FormInterface;
use TYPO3\CMS\Extbase\Error\Error;

class InvalidArgumentValueException extends FormzException
{
    const FIELD_VIEW_HELPER_EMPTY_LAYOUT = 'The layout name cannot be empty, please fill with a value.';

    const SIGNAL_NOT_ALLOWED = 'Trying to dispatch a signal that was not allowed by the middleware "%s". Authorized signals for this middleware are: "%s".';

    const FORM_NAME_EMPTY = 'The name of the form (type: "%s") can not be empty.';

    const AJAX_DATA_MAPPER_ERROR = 'Arguments mapping validation error %s';

    /**
     * @return self
     */
    final public static function fieldViewHelperEmptyLayout()
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(self::FIELD_VIEW_HELPER_EMPTY_LAYOUT, 1485786285);

        return $exception;
    }

    /**
     * @param SendsMiddlewareSignal $middleware
     * @return InvalidArgumentValueException
     */
    final public static function signalNotAllowed(SendsMiddlewareSignal $middleware)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::SIGNAL_NOT_ALLOWED,
            1490798201,
            [
                get_class($middleware),
                implode('", "', $middleware->getAllowedSignals())
            ]
        );

        return $exception;
    }

    /**
     * @param FormInterface $form
     * @return self
     */
    final public static function formNameEmpty(FormInterface $form)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FORM_NAME_EMPTY,
            1494515073,
            [get_class($form)]
        );

        return $exception;
    }

    /**
     * @param Error[] $errorsList
     * @return self
     */
    final public static function ajaxDataMapperError(array $errorsList)
    {
        $message = '';

        foreach ($errorsList as $key => $errors) {
            $message .= ' / ' . $key . ': ' . implode('; ', $errors);
        }

        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::AJAX_DATA_MAPPER_ERROR,
            1539693830,
            [$message]
        );

        return $exception;
    }
}
