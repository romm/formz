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
use Romm\Formz\Form\Definition\Step\Step\Step;
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
     * @var bool
     */
    protected $dummyMode;

    /**
     * @var callable[]
     */
    protected $fieldValidationCallback = [];

    /**
     * @var Step
     */
    protected $validatedStep;

    /**
     * @param FormObject $formObject
     * @param FormResult $formResult
     * @param bool $dummyMode
     */
    public function __construct(FormObject $formObject, FormResult $formResult, $dummyMode)
    {
        $this->formObject = $formObject;
        $this->formResult = $formResult;
        $this->dummyMode = $dummyMode;
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
     * @return bool
     */
    public function isDummyMode()
    {
        return $this->dummyMode;
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
