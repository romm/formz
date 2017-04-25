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
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\Definition\Field\Validation\Validation;
use Romm\Formz\Service\ArrayService;
use Romm\Formz\Service\ValidatorService;
use Romm\Formz\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This asset handler generates the JavaScript code which will initialize the
 * validation rules for every field of the form. Call the function
 * `getJavaScriptCode()`.
 *
 * It can also return the list of files which must be included in order to make
 * the form run correctly. Call the function `getJavaScriptValidationFiles()`.
 */
class FieldsValidationJavaScriptAssetHandler extends AbstractAssetHandler
{
    /**
     * @var array
     */
    protected $javaScriptValidationFiles;

    /**
     * Main function of this asset handler. See class description.
     *
     * @return string
     */
    public function getJavaScriptCode()
    {
        $fieldsJavaScriptCode = [];
        $formConfiguration = $this->getFormObject()->getDefinition();

        foreach ($formConfiguration->getFields() as $field) {
            $fieldsJavaScriptCode[] = $this->processField($field);
        }

        $formName = GeneralUtility::quoteJSvalue($this->getFormObject()->getName());
        $fieldsJavaScriptCode = implode(CRLF, $fieldsJavaScriptCode);

        return <<<JS
(function() {
    Fz.Form.get(
        $formName,
        function(form) {
            var field = null;

$fieldsJavaScriptCode
        }
    );
})();
JS;
    }

    /**
     * Will run the process for the given field.
     *
     * @param Field $field
     * @return string
     */
    protected function processField($field)
    {
        $javaScriptCode = [];
        $fieldName = $field->getName();

        foreach ($field->getValidation() as $validationName => $validationConfiguration) {
            $validatorClassName = $validationConfiguration->getClassName();

            if (in_array(AbstractValidator::class, class_parents($validatorClassName))) {
                $javaScriptCode[] = (string)$this->getInlineJavaScriptValidationCode($field, $validationName, $validationConfiguration);
            }
        }

        $javaScriptCode = implode(CRLF, $javaScriptCode);
        $javaScriptFieldName = GeneralUtility::quoteJSvalue($fieldName);

        return <<<JS
            /***************************
            * Field: "$fieldName"
            ****************************/
            field = form.getFieldByName($javaScriptFieldName);

            if (null !== field) {
$javaScriptCode
            }

JS;
    }

    /**
     * Generates the JavaScript code to add a validation rule to a field.
     *
     * @param Field      $field
     * @param string     $validationName         The name of the validation rule.
     * @param Validation $validatorConfiguration Contains the current validator configuration.
     * @return string
     */
    protected function getInlineJavaScriptValidationCode(Field $field, $validationName, Validation $validatorConfiguration)
    {
        $javaScriptValidationName = GeneralUtility::quoteJSvalue($validationName);
        $validatorName = addslashes($validatorConfiguration->getClassName());
        $validatorConfigurationFinal = $this->getValidationConfiguration($field, $validationName, $validatorConfiguration);
        $validatorConfigurationFinal = $this->handleValidationConfiguration($validatorConfigurationFinal);

        return <<<JS
                /*
                 * Validation rule "$validationName"
                 */
                field.addValidation($javaScriptValidationName, '$validatorName', $validatorConfigurationFinal);

JS;
    }

    /**
     * This function is here to help unit tests mocking.
     *
     * @param string $jsonValidationConfiguration
     * @return string
     */
    protected function handleValidationConfiguration($jsonValidationConfiguration)
    {
        return $jsonValidationConfiguration;
    }

    /**
     * Returns a JSON array containing the validation configuration needed by
     * JavaScript.
     *
     * @param Field      $field
     * @param string     $validationName
     * @param Validation $validatorConfiguration
     * @return string
     */
    protected function getValidationConfiguration(Field $field, $validationName, Validation $validatorConfiguration)
    {
        $acceptsEmptyValues = ValidatorService::get()->validatorAcceptsEmptyValues($validatorConfiguration->getClassName());

        /** @var FormzLocalizationJavaScriptAssetHandler $formzLocalizationJavaScriptAssetHandler */
        $formzLocalizationJavaScriptAssetHandler = $this->assetHandlerFactory->getAssetHandler(FormzLocalizationJavaScriptAssetHandler::class);

        $messages = $formzLocalizationJavaScriptAssetHandler->getTranslationKeysForFieldValidation($field, $validationName);

        return ArrayService::get()->arrayToJavaScriptJson([
            'options'            => $validatorConfiguration->getOptions(),
            'messages'           => $messages,
            'settings'           => $validatorConfiguration->toArray(),
            'acceptsEmptyValues' => $acceptsEmptyValues
        ]);
    }

    /**
     * @return array
     */
    public function getJavaScriptValidationFiles()
    {
        if (null === $this->javaScriptValidationFiles) {
            $this->javaScriptValidationFiles = [];

            $formConfiguration = $this->getFormObject()->getDefinition();

            foreach ($formConfiguration->getFields() as $field) {
                foreach ($field->getValidation() as $validationConfiguration) {
                    /** @var AbstractValidator $validatorClassName */
                    $validatorClassName = $validationConfiguration->getClassName();

                    if (in_array(AbstractValidator::class, class_parents($validatorClassName))) {
                        $this->javaScriptValidationFiles = array_merge($this->javaScriptValidationFiles, $validatorClassName::getJavaScriptValidationFiles());
                    }
                }
            }

            $this->javaScriptValidationFiles = array_unique($this->javaScriptValidationFiles);
        }

        return $this->javaScriptValidationFiles;
    }
}
