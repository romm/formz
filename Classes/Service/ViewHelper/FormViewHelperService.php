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

namespace Romm\Formz\Service\ViewHelper;

use Romm\Formz\Core\Core;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObject;
use Romm\Formz\Validation\Validator\Form\DefaultFormValidator;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Mvc\Request;

/**
 * This class contains methods that help view helpers to manipulate data and
 * know more things concerning the current form state.
 *
 * It is mainly configured inside the `FormViewHelper`, and used in other
 * view helpers.
 */
class FormViewHelperService implements SingletonInterface
{
    /**
     * @var bool
     */
    protected $formContext = false;

    /**
     * @var array|FormInterface
     */
    protected $formInstance;

    /**
     * @var bool
     */
    protected $formWasSubmitted = false;

    /**
     * @var FormResult
     */
    protected $formResult;

    /**
     * @var FormObject
     */
    protected $formObject;

    /**
     * Reset every state that can be used by this service.
     */
    public function resetState()
    {
        $this->formContext = false;
        $this->formWasSubmitted = false;
        $this->formObject = null;
        $this->formInstance = null;
        $this->formResult = null;
    }

    /**
     * Will activate the form context, changing the result returned by the
     * function `formContextExists()`.
     *
     * @throws \Exception
     * @return FormViewHelperService
     */
    public function activateFormContext()
    {
        if (true === $this->formContext) {
            throw new DuplicateEntryException(
                'You can not use a form view helper inside another one.',
                1465242575
            );
        }

        $this->formContext = true;

        return $this;
    }

    /**
     * Returns `true` if the `FormViewHelper` context exists.
     *
     * @return bool
     */
    public function formContextExists()
    {
        return $this->formContext;
    }

    /**
     * This function will check and inject the form instance and its submission
     * result.
     *
     * @param string  $formName
     * @param Request $originalRequest
     * @param         $formInstance
     */
    public function setUpData($formName, $originalRequest, $formInstance)
    {
        if (null !== $originalRequest
            && $originalRequest->hasArgument($formName)
        ) {
            /** @var array $formInstance */
            $formInstance = $originalRequest->getArgument($formName);

            $this->setFormInstance($formInstance);
            $this->setFormResult($this->formObject->getLastValidationResult());
            $this->markFormAsSubmitted();
        } elseif (null !== $formInstance) {
            $formValidator = $this->getFormValidator($formName);
            $formRequestResult = $formValidator->validateWithoutSavingResults($formInstance);

            $this->setFormInstance($formInstance);
            $this->setFormResult($formRequestResult);
        }
    }

    /**
     * Will mark the form as submitted (change the result returned by the
     * function `formWasSubmitted()`).
     */
    public function markFormAsSubmitted()
    {
        $this->formWasSubmitted = true;
    }

    /**
     * Returns `true` if the form was submitted by the user.
     *
     * @return bool
     */
    public function formWasSubmitted()
    {
        return $this->formWasSubmitted;
    }

    /**
     * @return array|FormInterface
     */
    public function getFormInstance()
    {
        return $this->formInstance;
    }

    /**
     * If the form was submitted by the user, contains the array containing the
     * submitted values.
     *
     * @param array|FormInterface $formInstance
     */
    public function setFormInstance($formInstance)
    {
        $this->formInstance = $formInstance;
    }

    /**
     * @return FormResult
     */
    public function getFormResult()
    {
        return $this->formResult;
    }

    /**
     * @param FormResult $formResult
     */
    public function setFormResult(FormResult $formResult)
    {
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
     * @param FormObject $formObject
     */
    public function setFormObject(FormObject $formObject)
    {
        $this->formObject = $formObject;
    }

    /**
     * @param string $formName
     * @return DefaultFormValidator
     */
    protected function getFormValidator($formName)
    {
        return Core::instantiate(DefaultFormValidator::class, ['name' => $formName]);
    }
}
