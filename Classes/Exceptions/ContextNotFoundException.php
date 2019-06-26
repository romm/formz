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

use Romm\Formz\ViewHelpers\FieldViewHelper;
use Romm\Formz\ViewHelpers\FormIdentifierHashViewHelper;
use Romm\Formz\ViewHelpers\FormViewHelper;
use Romm\Formz\ViewHelpers\OptionViewHelper;
use Romm\Formz\ViewHelpers\Slot\HasViewHelper;
use Romm\Formz\ViewHelpers\Slot\RenderViewHelper;
use Romm\Formz\ViewHelpers\SlotViewHelper;
use Romm\Formz\ViewHelpers\Step\SkipViewHelper;
use Romm\Formz\ViewHelpers\Step\SubstepViewHelper;

class ContextNotFoundException extends FormzException
{
    const FORM_CONTEXT_NOT_FOUND = 'The view helper "%s" must be used inside the view helper "%s".';

    const FIELD_CONTEXT_NOT_FOUND = 'The view helper "%s" must be used inside the view helper "%s".';

    const FORM_IDENTIFIER_FORM_CONTEXT_NOT_FOUND = 'The form context was not found for the view helper "%". You must either fill the arguments `form` and `name`, or use this view helper inside "%s"';

    /**
     * @code 1465243085
     *
     * @return self
     */
    final public static function fieldViewHelperFormContextNotFound()
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FORM_CONTEXT_NOT_FOUND,
            [FieldViewHelper::class, FormViewHelper::class]
        );

        return $exception;
    }
    /**
     * @code 1494337489
     *
     * @return self
     */
    final public static function substepViewHelperFormContextNotFound()
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FORM_CONTEXT_NOT_FOUND,
            [SubstepViewHelper::class, FormViewHelper::class]
        );

        return $exception;
    }

    /**
     * @code 1561020379
     *
     * @return self
     */
    final public static function skipViewHelperFormContextNotFound()
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FORM_CONTEXT_NOT_FOUND,
            [SkipViewHelper::class, FormViewHelper::class]
        );

        return $exception;
    }

    /**
     * @code 1465243287
     *
     * @return self
     */
    final public static function optionViewHelperFieldContextNotFound()
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_CONTEXT_NOT_FOUND,
            [OptionViewHelper::class, FieldViewHelper::class]
        );

        return $exception;
    }

    /**
     * @code 1488473956
     *
     * @return self
     */
    final public static function slotRenderViewHelperFieldContextNotFound()
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_CONTEXT_NOT_FOUND,
            [RenderViewHelper::class, FieldViewHelper::class]
        );

        return $exception;
    }

    /**
     * @code 1488474106
     *
     * @return self
     */
    final public static function slotViewHelperFieldContextNotFound()
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_CONTEXT_NOT_FOUND,
            [SlotViewHelper::class, FieldViewHelper::class]
        );

        return $exception;
    }

    /**
     * @code 1488988566
     *
     * @return self
     */
    final public static function slotHasViewHelperFieldContextNotFound()
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_CONTEXT_NOT_FOUND,
            [HasViewHelper::class, FieldViewHelper::class]
        );

        return $exception;
    }

    /**
     * @code 1490960228
     *
     * @return self
     */
    final public static function formIdentifierViewHelperFormContextNotFound()
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FORM_IDENTIFIER_FORM_CONTEXT_NOT_FOUND,
            [FormIdentifierHashViewHelper::class, FormViewHelper::class]
        );

        return $exception;
    }
}
