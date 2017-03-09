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
use Romm\Formz\Service\ArrayService;
use Romm\Formz\Service\MessageService;
use Romm\Formz\Service\ValidatorService;

/**
 * This asset handler will manage the translations which will be sent to FormZ
 * in JavaScript (`Formz.Localization`).
 *
 * The validation messages of the fields are handled in this class.
 */
class FormzLocalizationJavaScriptAssetHandler extends AbstractAssetHandler
{

    /**
     * Contains the full list of keys/translations.
     *
     * @var array
     */
    protected $translations = [];

    /**
     * Contains the list of keys which are bound to translations, for a field
     * validation. The value is an array of keys, because a validation rule may
     * have several messages.
     *
     * @var array
     */
    protected $translationKeysForFieldValidation = [];

    /**
     * Contains the list of fields validations which were already processed by
     * this asset handler.
     *
     * @var array
     */
    protected $injectedTranslationKeysForFieldValidation = [];

    /**
     * Will generate and return the JavaScript code which add all registered
     * translations to the JavaScript library.
     *
     * @return string
     */
    public function getJavaScriptCode()
    {
        $realTranslations = [];
        $translationsBinding = [];

        foreach ($this->translations as $key => $value) {
            $hash = sha1($value);
            $realTranslations[$hash] = $value;
            $translationsBinding[$key] = $hash;
        }

        $jsonRealTranslations = $this->handleRealTranslations(ArrayService::get()->arrayToJavaScriptJson($realTranslations));
        $jsonTranslationsBinding = $this->handleTranslationsBinding(ArrayService::get()->arrayToJavaScriptJson($translationsBinding));

        return <<<JS
Formz.Localization.addLocalization($jsonRealTranslations, $jsonTranslationsBinding);
JS;
    }

    /**
     * Returns the keys which are bound to translations, for a given field
     * validation rule.
     *
     * @param Field  $field
     * @param string $validationName
     * @return array
     */
    public function getTranslationKeysForFieldValidation(Field $field, $validationName)
    {
        $result = [];

        if (true === $field->hasValidation($validationName)) {
            $key = $field->getFieldName() . '-' . $validationName;

            $this->storeTranslationsForFieldValidation($field);

            $result = $this->translationKeysForFieldValidation[$key];
        }

        return $result;
    }

    /**
     * Will loop on each field of the given form, and get every translations for
     * the validation rules messages.
     *
     * @return $this
     */
    public function injectTranslationsForFormFieldsValidation()
    {
        $formConfiguration = $this->getFormObject()->getConfiguration();

        foreach ($formConfiguration->getFields() as $field) {
            $this->storeTranslationsForFieldValidation($field);
        }

        return $this;
    }

    /**
     * Will loop on each validation rule of the given field, and get the
     * translations of the rule messages.
     *
     * @param Field $field
     * @return $this
     */
    protected function storeTranslationsForFieldValidation(Field $field)
    {
        if (false === $this->translationsForFieldValidationWereInjected($field)) {
            $fieldName = $field->getFieldName();

            foreach ($field->getValidation() as $validationName => $validation) {
                $messages = ValidatorService::get()->getValidatorMessages($validation->getClassName(), $validation->getMessages());

                foreach ($messages as $key => $message) {
                    $message = MessageService::get()->parseMessageArray($message, ['{0}', '{1}', '{2}', '{3}', '{4}', '{5}', '{6}', '{7}', '{8}', '{9}', '{10}']);

                    $localizationKey = $this->getIdentifierForFieldValidationName($field, $validationName, $key);
                    $this->addTranslation($localizationKey, $message);
                    $messages[$key] = $localizationKey;
                }

                $this->translationKeysForFieldValidation[$fieldName . '-' . $validationName] = $messages;

                $key = $this->getFormObject()->getClassName() . '-' . $field->getFieldName();
                $this->injectedTranslationKeysForFieldValidation[$key] = true;
            }
        }

        return $this;
    }

    /**
     * Adds a global translation value which will be added to the FormZ
     * JavaScript localization service.
     *
     * @param string $key
     * @param string $value
     */
    protected function addTranslation($key, $value)
    {
        $this->translations[(string)$key] = (string)$value;
    }

    /**
     * Checks if the given field validation rules were already handled by this
     * asset handler.
     *
     * @param Field $field
     * @return bool
     */
    protected function translationsForFieldValidationWereInjected(Field $field)
    {
        $key = $this->getFormObject()->getClassName() . '-' . $field->getFieldName();

        return true === isset($this->injectedTranslationKeysForFieldValidation[$key]);
    }

    /**
     * @param Field  $field
     * @param string $validationName
     * @param string $messageKey
     * @return string
     */
    protected function getIdentifierForFieldValidationName(Field $field, $validationName, $messageKey)
    {
        return str_replace(['\\', '_'], '', $this->getFormObject()->getClassName()) . '-' . $field->getFieldName() . '-' . $validationName . '-' . $messageKey;
    }

    /**
     * This function is here to help unit tests mocking.
     *
     * @param string $realTranslations
     * @return string
     */
    protected function handleRealTranslations($realTranslations)
    {
        return $realTranslations;
    }

    /**
     * This function is here to help unit tests mocking.
     *
     * @param string $translationsBinding
     * @return string
     */
    protected function handleTranslationsBinding($translationsBinding)
    {
        return $translationsBinding;
    }
}
