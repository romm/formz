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

namespace Romm\Formz\Validation\Validator\Form\DataObject;

use Romm\Formz\Error\FormResult;
use Romm\Formz\Form\Definition\Step\Step\Step;

class FormValidatorDataObject
{
    /**
     * @var FormResult
     */
    protected $formResult;

    /**
     * @var callable[]
     */
    protected $fieldValidationCallback = [];

    /**
     * @var Step
     */
    protected $validatedStep;

    /**
     * @param FormResult $formResult
     */
    public function __construct(FormResult $formResult)
    {
        $this->formResult = $formResult;
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

    /**
     * @return Step|null
     */
    public function getValidatedStep()
    {
        return $this->validatedStep;
    }

    /**
     * @param Step $validatedStep
     */
    public function setValidatedStep(Step $validatedStep)
    {
        $this->validatedStep = $validatedStep;
    }
}
