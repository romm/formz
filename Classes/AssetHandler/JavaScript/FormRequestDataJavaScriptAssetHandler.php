<?php
/*
 * 2017 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Formz project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\AbstractAssetHandler;
use Romm\Formz\Service\ArrayService;
use Romm\Formz\Service\MessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Message;
use TYPO3\CMS\Extbase\Error\Result;

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
        $originalRequest = $this->getControllerContext()
            ->getRequest()
            ->getOriginalRequest();

        $formWasSubmitted = (null !== $originalRequest)
            ? 'true'
            : 'false';

        if ($formWasSubmitted) {
            $submittedFormValues = ArrayService::get()->arrayToJavaScriptJson($this->getSubmittedFormValues());
            $fieldsExistingMessages = ArrayService::get()->arrayToJavaScriptJson($this->getFieldsExistingMessages());
        }

        $formName = GeneralUtility::quoteJSvalue($this->getFormObject()->getName());

        $javaScriptCode = <<<JS
(function() {
    Formz.Form.beforeInitialization($formName, function(form) {
        form.injectRequestData($submittedFormValues, $fieldsExistingMessages, $formWasSubmitted)
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
        $request = $this->getControllerContext()
            ->getRequest();

        if (null !== $request->getOriginalRequest()) {
            $requestResult = $request->getOriginalRequestMappingResults();
            /** @var Result[] $formFieldsResult */
            $formFieldsResult = $requestResult->forProperty($this->getFormObject()->getName())->getSubResults();

            foreach ($this->getFormObject()->getProperties() as $fieldName) {
                if (array_key_exists($fieldName, $formFieldsResult)) {
                    $messages = [];
                    $result = $formFieldsResult[$fieldName];

                    if ($result->hasErrors()) {
                        $messages['errors'] = $this->formatMessages($result->getErrors());
                    }

                    if ($result->hasWarnings()) {
                        $messages['warnings'] = $this->formatMessages($result->getWarnings());
                    }

                    if ($result->hasNotices()) {
                        $messages['notices'] = $this->formatMessages($result->getNotices());
                    }

                    $fieldsMessages[$fieldName] = $messages;
                }
            }
        }

        return $fieldsMessages;
    }

    /**
     * @param Message[] $messages
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
}
