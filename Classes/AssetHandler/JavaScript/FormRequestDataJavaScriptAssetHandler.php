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
use Romm\Formz\Form\FormInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Validation\Error;

/**
 * Will handle the "one time run" data needed by JavaScript: the submitted
 * values, and others.
 */
class FormRequestDataJavaScriptAssetHandler extends AbstractJavaScriptAssetHandler
{

    /**
     * See class description.
     *
     * @param FormInterface $formInstance
     * @return string
     */
    public function getFormRequestDataJavaScriptCode($formInstance)
    {
        $submittedFormValues = Core::get()->arrayToJavaScriptJson($this->getSubmittedFormValues($formInstance));
        $fieldsExistingErrors = Core::get()->arrayToJavaScriptJson($this->getFieldsExistingErrors());

        $originalRequest = $this->assetHandlerFactory
            ->getControllerContext()
            ->getRequest()
            ->getOriginalRequest();
        $formWasSubmitted = (null !== $originalRequest)
            ? 'true'
            : 'false';

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
     * @param FormInterface $formInstance
     * @return array
     */
    protected function getSubmittedFormValues($formInstance)
    {
        $result = [];
        $formName = $this->getFormObject()->getName();

        $originalRequest = $this->assetHandlerFactory
            ->getControllerContext()
            ->getRequest()
            ->getOriginalRequest();
        if (null !== $originalRequest
            && $originalRequest->hasArgument($formName)
        ) {
            $result = $originalRequest->getArgument($formName);
        } elseif (is_object($formInstance)) {
            foreach ($this->getFormObject()->getProperties() as $fieldName) {
                $result[$fieldName] = ObjectAccess::getProperty($formInstance, $fieldName);
            }
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
        $controllerContext = $this->assetHandlerFactory->getControllerContext();
        $formObject = $this->assetHandlerFactory->getFormObject();
        $formConfiguration = $formObject->getConfiguration();

        if (null !== $controllerContext->getRequest()->getOriginalRequest()) {
            $requestResult = $controllerContext->getRequest()->getOriginalRequestMappingResults();
            /** @var Result[] $formFieldsResult */
            $formFieldsResult = $requestResult->forProperty($this->getFormObject()->getName())->getSubResults();

            foreach ($formConfiguration->getFields() as $field) {
                $fieldName = $field->getFieldName();
                if (isset($formFieldsResult[$fieldName])) {
                    if ($formFieldsResult[$fieldName]->hasErrors()) {
                        $fieldsErrors[$fieldName] = [];
                        foreach ($formFieldsResult[$fieldName]->getErrors() as $error) {
                            /** @var Error $error */
                            list($validationName, $errorName) = explode(':', $error->getTitle());
                            $fieldsErrors[$fieldName][$validationName] = [$errorName => $error->getMessage()];
                        }
                    }
                }
            }
        }

        return $fieldsErrors;
    }
}
