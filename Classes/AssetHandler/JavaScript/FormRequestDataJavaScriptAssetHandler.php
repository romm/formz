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

namespace Romm\Formz\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\AbstractAssetHandler;
use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Error\FormzMessageInterface;
use Romm\Formz\Service\ArrayService;
use Romm\Formz\Service\MessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Will handle the "one time run" data needed by JavaScript: the submitted
 * values, and others.
 */
class FormRequestDataJavaScriptAssetHandler extends AbstractAssetHandler
{

    /**
     * See class description.
     *
     * @return string
     */
    public function getFormRequestDataJavaScriptCode()
    {
        $submittedFormValues = [];
        $fieldsExistingMessages = [];
        $deactivatedFields = [];
        $formWasSubmitted = 'false';

        if ($this->getFormObject()->formWasSubmitted()) {
            $formWasSubmitted = 'true';
            $submittedFormValues = $this->getSubmittedFormValues();
            $fieldsExistingMessages = $this->getFieldsExistingMessages();
            $deactivatedFields = $this->getDeactivatedFields();
        }

        $submittedFormValues = ArrayService::get()->arrayToJavaScriptJson($submittedFormValues);
        $fieldsExistingMessages = ArrayService::get()->arrayToJavaScriptJson($fieldsExistingMessages);
        $deactivatedFields = ArrayService::get()->arrayToJavaScriptJson($deactivatedFields);

        $formName = GeneralUtility::quoteJSvalue($this->getFormObject()->getName());

        $javaScriptCode = <<<JS
(function() {
    Fz.Form.beforeInitialization($formName, function(form) {
        form.injectRequestData($submittedFormValues, $fieldsExistingMessages, $formWasSubmitted, $deactivatedFields)
    });
})();
JS;

        return $javaScriptCode;
    }

    /**
     * Will fetch the values of the fields of the submitted form, if a form has
     * been submitted.
     *
     * @return array
     */
    protected function getSubmittedFormValues()
    {
        $result = [];
        $formName = $this->getFormObject()->getName();
        $originalRequest = $this->getControllerContext()
            ->getRequest()
            ->getOriginalRequest();

        if (null !== $originalRequest
            && $originalRequest->hasArgument($formName)
        ) {
            $result = $originalRequest->getArgument($formName);
        }

        return $result;
    }

    /**
     * This function checks every message which may exist on every property of
     * the form (used to tell to JavaScript which messages already exist).
     *
     * @return array
     */
    protected function getFieldsExistingMessages()
    {
        $fieldsMessages = [];
        $formObject = $this->getFormObject();

        if ($formObject->formWasSubmitted()
            && $formObject->hasFormResult()
        ) {
            foreach ($this->getFormObject()->getConfiguration()->getFields() as $field) {
                $fieldsMessages[$field->getName()] = $this->getSingleFieldExistingMessages($field);
            }
        }

        return $fieldsMessages;
    }

    /**
     * @param Field $field
     * @return array
     */
    protected function getSingleFieldExistingMessages(Field $field)
    {
        $formResult = $this->getFormObject()->getFormResult();
        $result = $formResult->forProperty($field->getName());
        $messages = [];

        if ($result->hasErrors()) {
            $messages['errors'] = $this->formatMessages($result->getErrors());
        }

        if ($result->hasWarnings()) {
            $messages['warnings'] = $this->formatMessages($result->getWarnings());
        }

        if ($result->hasNotices()) {
            $messages['notices'] = $this->formatMessages($result->getNotices());
        }

        return $messages;
    }

    /**
     * @param FormzMessageInterface[] $messages
     * @return array
     */
    protected function formatMessages(array $messages)
    {
        $sortedMessages = [];

        foreach ($messages as $message) {
            $validationName = MessageService::get()->getMessageValidationName($message);
            $messageKey = MessageService::get()->getMessageKey($message);

            if (false === isset($sortedMessages[$validationName])) {
                $sortedMessages[$validationName] = [];
            }

            $sortedMessages[$validationName][$messageKey] = $message->render();
        }

        return $sortedMessages;
    }

    /**
     * @return array
     */
    protected function getDeactivatedFields()
    {
        return ($this->getFormObject()->hasFormResult())
            ? array_keys($this->getFormObject()->getFormResult()->getDeactivatedFields())
            : [];
    }
}
