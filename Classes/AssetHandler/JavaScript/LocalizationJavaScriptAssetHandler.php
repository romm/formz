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
use Romm\Formz\Form\Definition\Field\Validation\Validator;
use Romm\Formz\Service\ArrayService;
use Romm\Formz\Service\HashService;
use Romm\Formz\Service\MessageService;
use Romm\Formz\Service\ValidatorService;

/**
 * This asset handler will manage the translations which will be sent to FormZ
 * in JavaScript (`Formz.Localization`).
 *
 * The validator messages of the fields are handled in this class.
 */
class LocalizationJavaScriptAssetHandler extends AbstractAssetHandler
{

    /**
     * Contains the full list of keys/translations.
     *
     * @var array
     */
    protected $translations = [];

    /**
     * Contains the list of keys which are bound to translations, for a field
     * validator. The value is an array of keys, because a validator may have
     * several messages.
     *
     * @var array
     */
    protected $translationKeysForFieldValidator = [];

    /**
     * Contains the list of fields validators which were already processed by
     * this asset handler.
     *
     * @var array
     */
    protected $injectedTranslationKeysForFieldValidator = [];

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
            $hash = HashService::get()->getHash($value);
            $realTranslations[$hash] = $value;
            $translationsBinding[$key] = $hash;
        }

        $jsonRealTranslations = $this->handleRealTranslations(ArrayService::get()->arrayToJavaScriptJson($realTranslations));
        $jsonTranslationsBinding = $this->handleTranslationsBinding(ArrayService::get()->arrayToJavaScriptJson($translationsBinding));

        return <<<JS
Fz.Localization.addLocalization($jsonRealTranslations, $jsonTranslationsBinding);
JS;
    }

    /**
     * Returns the keys which are bound to translations, for a given field
     * validator.
     *
     * @param Field     $field
     * @param Validator $validator
     * @return array
     */
    public function getTranslationKeysForFieldValidator(Field $field, Validator $validator)
    {
        $key = $field->getName() . '-' . $validator->getName();

        $this->storeTranslationsForFieldValidator($field);

        return $this->translationKeysForFieldValidator[$key];
    }

    /**
     * Will loop on each field of the given form, and get every translations for
     * the validator rules messages.
     *
     * @return $this
     */
    public function injectTranslationsForFormFieldsValidator()
    {
        $formConfiguration = $this->getFormObject()->getDefinition();

        foreach ($formConfiguration->getFields() as $field) {
            $this->storeTranslationsForFieldValidator($field);
        }

        return $this;
    }

    /**
     * Will loop on each validator rule of the given field, and get the
     * translations of the rule messages.
     *
     * @param Field $field
     * @return $this
     */
    protected function storeTranslationsForFieldValidator(Field $field)
    {
        if (false === $this->translationsForFieldValidatorWereInjected($field)) {
            $fieldName = $field->getName();

            foreach ($field->getValidators() as $validator) {
                $messages = ValidatorService::get()->getValidatorMessages($validator);

                foreach ($messages as $key => $message) {
                    $message = MessageService::get()->parseMessageArray($message, ['{0}', '{1}', '{2}', '{3}', '{4}', '{5}', '{6}', '{7}', '{8}', '{9}', '{10}']);

                    $localizationKey = $this->getIdentifierForFieldValidator($field, $validator, $key);
                    $this->addTranslation($localizationKey, $message);
                    $messages[$key] = $localizationKey;
                }

                $this->translationKeysForFieldValidator[$fieldName . '-' . $validator->getName()] = $messages;

                $key = $this->getFormObject()->getClassName() . '-' . $field->getName();
                $this->injectedTranslationKeysForFieldValidator[$key] = true;
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
     * Checks if the given field validator were already handled by this
     * asset handler.
     *
     * @param Field $field
     * @return bool
     */
    protected function translationsForFieldValidatorWereInjected(Field $field)
    {
        $key = $this->getFormObject()->getClassName() . '-' . $field->getName();

        return true === isset($this->injectedTranslationKeysForFieldValidator[$key]);
    }

    /**
     * @param Field     $field
     * @param Validator $validator
     * @param string    $messageKey
     * @return string
     */
    protected function getIdentifierForFieldValidator(Field $field, Validator $validator, $messageKey)
    {
        return str_replace(['\\', '_'], '', $this->getFormObject()->getClassName()) . '-' . $field->getName() . '-' . $validator->getName() . '-' . $messageKey;
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