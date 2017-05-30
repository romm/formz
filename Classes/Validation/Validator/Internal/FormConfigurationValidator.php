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

namespace Romm\Formz\Validation\Validator\Internal;

use Romm\Formz\Form\Definition\FormDefinition;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class FormConfigurationValidator extends AbstractValidator
{
    /**
     * @param FormDefinition $form
     */
    public function isValid($form)
    {
        if (false === $form instanceof FormDefinition) {
            return;
        }

        if ($form->hasSteps()) {
            $stepValidator = new StepValidator([], $form);
            $result = $stepValidator->validate($form->getSteps());

            $this->result->forProperty('step')->merge($result);
        }
    }
}
