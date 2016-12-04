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

namespace Romm\Formz\Condition\Processor\DataObject;

use Romm\Formz\Form\FormInterface;
use Romm\Formz\Validation\Validator\Form\AbstractFormValidator;

class PhpConditionDataObject
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var AbstractFormValidator
     */
    protected $formValidator;

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
    public function setForm($form)
    {
        $this->form = $form;
    }

    /**
     * @return AbstractFormValidator
     */
    public function getFormValidator()
    {
        return $this->formValidator;
    }

    /**
     * @param AbstractFormValidator $formValidator
     */
    public function setFormValidator($formValidator)
    {
        $this->formValidator = $formValidator;
    }
}
