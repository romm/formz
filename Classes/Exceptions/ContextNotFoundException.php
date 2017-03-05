<?php
/*
 * 2016 Romain CANON <romain.hydrocanon@gmail.com>
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

use Romm\Formz\ViewHelpers\FieldViewHelper;
use Romm\Formz\ViewHelpers\FormViewHelper;
use Romm\Formz\ViewHelpers\OptionViewHelper;
use Romm\Formz\ViewHelpers\RenderSectionViewHelper;
use Romm\Formz\ViewHelpers\SectionViewHelper;

class ContextNotFoundException extends FormzException
{
    const FORM_CONTEXT_NOT_FOUND = 'The view helper "%s" must be used inside the view helper "%s".';

    const FIELD_CONTEXT_NOT_FOUND = 'The view helper "%s" must be used inside the view helper "%s".';

    /**
     * @code 1465243085
     *
     * @return ContextNotFoundException
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
     * @code 1465243287
     *
     * @return ContextNotFoundException
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
     * @return ContextNotFoundException
     */
    final public static function renderSectionViewHelperFieldContextNotFound()
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_CONTEXT_NOT_FOUND,
            [RenderSectionViewHelper::class, FieldViewHelper::class]
        );

        return $exception;
    }

    /**
     * @code 1488474106
     *
     * @return ContextNotFoundException
     */
    final public static function sectionViewHelperFieldContextNotFound()
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_CONTEXT_NOT_FOUND,
            [SectionViewHelper::class, FieldViewHelper::class]
        );

        return $exception;
    }
}
