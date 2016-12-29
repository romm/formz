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

namespace Romm\Formz\AssetHandler\JavaScript;

use Romm\Formz\Core\Core;
use Romm\Formz\Error\FormzMessageInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Result;

/**
 * Will handle the "one time run" data needed by JavaScript: the submitted
 * values, and others.
 */
class FormRequestDataJavaScriptAssetHandler extends AbstractJavaScriptAssetHandler
{

    /**
     * See class description.
     *
     * @return string
     */
    public function getFormRequestDataJavaScriptCode()
    {
        $submittedFormValues = [];
        $fieldsExistingErrors = [];
        $originalRequest = $this->getControllerContext()
            ->getRequest()
            ->getOriginalRequest();

        $formWasSubmitted = (null !== $originalRequest)
            ? 'true'
            : 'false';

        if ($formWasSubmitted) {
            $submittedFormValues = Core::get()->arrayToJavaScriptJson($this->getSubmittedFormValues());
            $fieldsExistingErrors = Core::get()->arrayToJavaScriptJson($this->getFieldsExistingErrors());
        }

        $formName = GeneralUtility::quoteJSvalue($this->getFormObject()->getName());

        $javaScriptCode = <<<JS
(function() {
    Formz.Form.beforeInitialization($formName, function(form) {
        form.injectRequestData($submittedFormValues, $fieldsExistingErrors, $formWasSubmitted)
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
     * This function checks every error which may exist on every property of the
     * form (used to tell to JavaScript which errors already exist).
     *
     * @return array
     */
    protected function getFieldsExistingErrors()
    {
        $fieldsErrors = [];
        $request = $this->getControllerContext()
            ->getRequest();

        if (null !== $request->getOriginalRequest()) {
            $requestResult = $request->getOriginalRequestMappingResults();
            /** @var Result[] $formFieldsResult */
            $formFieldsResult = $requestResult->forProperty($this->getFormObject()->getName())->getSubResults();

            foreach ($this->getFormObject()->getProperties() as $fieldName) {
                if (array_key_exists($fieldName, $formFieldsResult)
                    && $formFieldsResult[$fieldName]->hasErrors()
                ) {
                    $fieldsErrors[$fieldName] = [];

                    foreach ($formFieldsResult[$fieldName]->getErrors() as $error) {
                        /** @var Error $error */
                        $validationName = ($error instanceof FormzMessageInterface)
                            ? $error->getValidationName()
                            : 'unknown';

                        $messageKey = ($error instanceof FormzMessageInterface)
                            ? $error->getMessageKey()
                            : 'unknown';

                        $fieldsErrors[$fieldName][$validationName] = [$messageKey => $error->render()];
                    }
                }
            }
        }

        return $fieldsErrors;
    }
}
