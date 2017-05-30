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
use Romm\Formz\Form\Definition\Step\Steps;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class StepValidator extends AbstractValidator
{
    /**
     * @var FormDefinition
     */
    protected $form;

    /**
     * @param array $options
     * @param FormDefinition  $form
     */
    public function __construct(array $options = [], FormDefinition $form)
    {
        parent::__construct($options);

        $this->form = $form;
    }

    /**
     * @param Steps $step
     */
    public function isValid($step)
    {
        if (false === $step instanceof Steps) {
            return;
        }

        if (false === $this->form->hasPersistence()) {
            $this->addError(
                'In order to use steps, you first need to register at least one persistence service for this form.',
                1491828562
            );
        }
    }
}
