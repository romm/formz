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

namespace Romm\Formz\Middleware\Processor;

/**
 * This interface must be implemented by middlewares that should not be called
 * when being in the "single field validation context", for instance when an
 * Ajax call is made to validate a single field.
 *
 * This will automatically remove the middleware from the list of middlewares of
 * the current form.
 *
 * @see MiddlewareProcessor::activateSingleFieldValidationContext()
 * @see MiddlewareProcessor::inSingleFieldValidationContext()
 */
interface RemoveFromSingleFieldValidationContext
{
}
