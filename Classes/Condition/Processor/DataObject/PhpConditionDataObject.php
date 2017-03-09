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

namespace Romm\Formz\Condition\Processor\DataObject;

use Romm\Formz\Form\FormInterface;
use Romm\Formz\Validation\Validator\Form\FormValidatorExecutor;

class PhpConditionDataObject
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var FormValidatorExecutor
     */
    protected $formValidator;

    /**
     * @param FormInterface         $form
     * @param FormValidatorExecutor $formValidator
     */
    public function __construct(FormInterface $form, FormValidatorExecutor $formValidator)
    {
        $this->form = $form;
        $this->formValidator = $formValidator;
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param FormInterface $form
     */
    public function setForm(FormInterface $form)
    {
        $this->form = $form;
    }

    /**
     * @return FormValidatorExecutor
     */
    public function getFormValidator()
    {
        return $this->formValidator;
    }

    /**
     * @param FormValidatorExecutor $formValidator
     */
    public function setFormValidator(FormValidatorExecutor $formValidator)
    {
        $this->formValidator = $formValidator;
    }
}
