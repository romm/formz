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

namespace Romm\Formz\Domain\Middleware\FormValidation;

use Romm\Formz\Middleware\Option\AbstractOptionDefinition;
use Romm\Formz\Validation\Validator\Form\DefaultFormValidator;

class FormValidationMiddlewareOption extends AbstractOptionDefinition
{
    /**
     * @var string
     * @validate Romm.ConfigurationObject:ClassExtends(class=Romm\Formz\Validation\Validator\Form\AbstractFormValidator)
     * @validate NotEmpty
     */
    protected $formValidatorClassName = DefaultFormValidator::class;

    /**
     * @return string
     */
    public function getFormValidatorClassName()
    {
        return $this->formValidatorClassName;
    }
}
