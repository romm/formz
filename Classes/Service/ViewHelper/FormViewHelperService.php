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

namespace Romm\Formz\Service\ViewHelper;

use Romm\Formz\AssetHandler\Html\DataAttributesAssetHandler;
use Romm\Formz\Behaviours\BehavioursManager;
use Romm\Formz\Core\Core;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Validation\Validator\Form\DefaultFormValidator;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;

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
     * @var FormObject
     */
    protected $formObject;

    /**
     * Reset every state that can be used by this service.
     */
    public function resetState()
    {
        $this->formContext = false;
        $this->formObject = null;
    }

    /**
     * Will activate the form context, changing the result returned by the
     * function `formContextExists()`.
     *
     * @return FormViewHelperService
     * @throws DuplicateEntryException
     */
    public function activateFormContext()
    {
        if (true === $this->formContext) {
            throw DuplicateEntryException::duplicatedFormContext();
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
     * Will loop on the submitted form fields and apply behaviours if their
     * configuration contains.
     *
     * @param ControllerContext $controllerContext
     */
    public function applyBehavioursOnSubmittedForm(ControllerContext $controllerContext)
    {
        if ($this->formObject->formWasSubmitted()) {
            $request = $controllerContext->getRequest()->getOriginalRequest();
            $formName = $this->formObject->getName();

            if ($request
                && $request->hasArgument($formName)
            ) {
                /** @var BehavioursManager $behavioursManager */
                $behavioursManager = GeneralUtility::makeInstance(BehavioursManager::class);

                /** @var array $originalForm */
                $originalForm = $request->getArgument($formName);

                $formProperties = $behavioursManager->applyBehaviourOnPropertiesArray(
                    $originalForm,
                    $this->formObject->getDefinition()
                );

                $request->setArgument($formName, $formProperties);
            }
        }
    }

    /**
     * Fetches all data attributes that are bound to the form: fields values,
     * validation result and others.
     *
     * @param DataAttributesAssetHandler $dataAttributesAssetHandler
     * @return array
     */
    public function getDataAttributes(DataAttributesAssetHandler $dataAttributesAssetHandler)
    {
        $dataAttributes = [];

        if ($this->formObject->hasForm()) {
            /*
             * Getting the data attributes for the form values. It is needed to
             * have a validation result because a field can be deactivated (in
             * that case, the data attribute for this field is removed).
             */
            if (false === $this->formObject->formWasValidated()) {
                $formResult = $this->getFormValidationResult();
            } else {
                $formResult = $this->formObject->getFormResult();
            }

            $dataAttributes += $dataAttributesAssetHandler->getFieldsValuesDataAttributes($formResult);
        }

        if (true === $this->formObject->formWasSubmitted()) {
            $dataAttributes += $dataAttributesAssetHandler->getFieldSubmissionDoneDataAttribute();
        }

        if (true === $this->formObject->formWasValidated()) {
            $dataAttributes += $dataAttributesAssetHandler->getFieldsValidDataAttributes();
            $dataAttributes += $dataAttributesAssetHandler->getFieldsMessagesDataAttributes();
        }

        return $dataAttributes;
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
     * @return FormResult
     */
    protected function getFormValidationResult()
    {
        $formValidator = $this->getFormValidator($this->formObject->getName());

        return $formValidator->validate($this->formObject->getForm());
    }

    /**
     * @param string $formName
     * @return DefaultFormValidator
     */
    protected function getFormValidator($formName)
    {
        /** @var DefaultFormValidator $validation */
        $validation = Core::instantiate(
            DefaultFormValidator::class,
            [
                'name'  => $formName,
                'dummy' => true
            ]
        );

        return $validation;
    }
}
