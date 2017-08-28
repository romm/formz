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

namespace Romm\Formz\Validation\Form\DataObject;

use Romm\Formz\Error\FormResult;
use Romm\Formz\Form\FormObject\FormObject;

class FormValidatorDataObject
{
    /**
     * @var FormObject
     */
    protected $formObject;

    /**
     * @var FormResult
     */
    protected $formResult;

    /**
     * @var callable[]
     */
    protected $fieldValidationCallback = [];

    /**
     * @param FormObject $formObject
     * @param FormResult $formResult
     */
    public function __construct(FormObject $formObject, FormResult $formResult)
    {
        $this->formObject = $formObject;
        $this->formResult = $formResult;
    }

    /**
     * @return FormObject
     */
    public function getFormObject()
    {
        return $this->formObject;
    }

    /**
     * @return FormResult
     */
    public function getFormResult()
    {
        return $this->formResult;
    }

    /**
     * @return callable[]
     */
    public function getFieldValidationCallbacks()
    {
        return $this->fieldValidationCallback;
    }

    /**
     * @param callable $fieldValidationCallback
     */
    public function addFieldValidationCallback(callable $fieldValidationCallback)
    {
        $this->fieldValidationCallback[] = $fieldValidationCallback;
    }
}
