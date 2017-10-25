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

namespace Romm\Formz\Service\ViewHelper\Form;

use DateTime;
use Romm\Formz\AssetHandler\Html\DataAttributesAssetHandler;
use Romm\Formz\Behaviours\BehavioursManager;
use Romm\Formz\Core\Core;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Form\Definition\Step\Step\Step;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Validation\Validator\Form\AbstractFormValidator;
use Romm\Formz\Validation\Validator\Form\DefaultFormValidator;
use Traversable;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3\CMS\Fluid\ViewHelpers\FormViewHelper;

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
     * @var Request
     */
    protected $request;

    /**
     * @var Result
     */
    protected $result;

    /**
     * Reset every state that can be used by this service.
     */
    public function resetState()
    {
        $this->formContext = false;
        $this->formObject = null;
        $this->request = null;
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
        $this->result = new Result;

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
     */
    public function applyBehavioursOnSubmittedForm()
    {
        if ($this->formObject->formWasSubmitted()) {
            $request = $this->request->getOriginalRequest();
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
     * Takes care of injecting data for the form.
     *
     * If the form was generated using a content object, information about it
     * are injected, to be retrieved later to be able for instance to fetch the
     * object settings (TypoScript, FlexForm, ...).
     */
    public function injectFormRequestData()
    {
        if (false === $this->formObject->hasForm()) {
            return;
        }

        $requestData = $this->formObject->getRequestData();

        $currentStepIdentifier = $this->getCurrentStep()
            ? $this->getCurrentStep()->getIdentifier()
            : null;
        $requestData->setCurrentStepIdentifier($currentStepIdentifier);

        /** @var ConfigurationManager $configurationManager */
        $configurationManager = Core::instantiate(ConfigurationManagerInterface::class);

        $contentObject = $configurationManager->getContentObject();

        if (null !== $contentObject) {
            $requestData->setContentObjectTable($contentObject->getCurrentTable());
            $requestData->setContentObjectUid($contentObject->data['uid']);
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

        $dataAttributes = $this->formatDataAttributes($dataAttributes);

        return $dataAttributes;
    }

    /**
     * Checks the type of every data attribute and formats it if needed.
     *
     * @param array $dataAttributes
     * @return array
     */
    protected function formatDataAttributes(array $dataAttributes)
    {
        foreach ($dataAttributes as $key => $value) {
            if (is_array($value) || $value instanceof Traversable) {
                $dataAttributes[$key] = implode(',', $value);
            } elseif ($value instanceof DateTime) {
                $format = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] . ' ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'];
                $dataAttributes[$key] = $value->format($format);
            } elseif (false === is_string($value)) {
                $dataAttributes[$key] = (string)$value;
            }
        }

        return $dataAttributes;
    }

    /**
     * Checks if the form uses steps, in which case the current step is needed
     * in order to display the form. If the step is not found, an exception is
     * thrown.
     */
    public function checkStepDefinition()
    {
        if ($this->formObject->getDefinition()->hasSteps()
            && null === $this->getCurrentStep()
        ) {
            throw new \Exception('todo'); // @todo
        }
    }

    /**
     * Will check all the fields that have been added below the form view
     * helper: each field that is found in the form definition and is *not*
     * supported by the current step will add an error and cancel the form
     * rendering.
     *
     * @param ViewHelperVariableContainer $variableContainer
     */
    public function checkStepFields(ViewHelperVariableContainer $variableContainer)
    {
        $currentStep = $this->getCurrentStep();

        if (null === $currentStep) {
            return;
        }

        $unsupportedFieldsList = [];
        $formDefinition = $this->formObject->getDefinition();
        $fieldNames = $this->getCurrentFormFieldNames($variableContainer);

        foreach ($fieldNames as $fieldName) {
            if (false === $formDefinition->hasField($fieldName)) {
                continue;
            }

            $field = $formDefinition->getField($fieldName);

            if (false === $currentStep->supportsField($field)) {
                $unsupportedFieldsList[] = $fieldName;
            }
        }

        if ($unsupportedFieldsList) {
            $error = new Error(
                'The following fields are not supported by the step "%s": "%s". Please add these fields to the supported fields list of the step in order to render it in your template.',
                1494430935,
                [$currentStep->getIdentifier(), implode('", "', $unsupportedFieldsList)]
            );
            $this->result->addError($error);
        }
    }

    /**
     * Returns the list of fields that have been added below the form view
     * helper.
     *
     * @param ViewHelperVariableContainer $variableContainer
     * @return array
     */
    public function getCurrentFormFieldNames(ViewHelperVariableContainer $variableContainer)
    {
        $formFieldNames = $variableContainer->get(FormViewHelper::class, 'formFieldNames');
        $cleanFormFieldNames = [];

        foreach ($formFieldNames as $fieldName) {
            $explode = explode('[', $fieldName);

            if (count($explode) >= 3) {
                $formName = rtrim($explode[1], ']');
                $fieldName = rtrim($explode[2], ']');

                if ($formName === $this->formObject->getName()
                    && $fieldName !== '__identity'
                ) {
                    $cleanFormFieldNames[$fieldName] = $fieldName;
                }
            }
        }

        return $cleanFormFieldNames;
    }

    /**
     * @return Step|null
     */
    public function getCurrentStep()
    {
        return $this->formObject->fetchCurrentStep($this->request)->getCurrentStep();
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
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return Result
     */
    public function getResult()
    {
        return $this->result;
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
     * @return AbstractFormValidator
     */
    protected function getFormValidator($formName)
    {
        /** @var AbstractFormValidator $validator */
        $validator = Core::instantiate(
            DefaultFormValidator::class,
            [
                'name'  => $formName,
                'dummy' => true
            ]
        );

        return $validator;
    }
}
