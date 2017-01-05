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

namespace Romm\Formz\Validation\Validator\Internal;

use Romm\Formz\Configuration\Form\Field\Validation\Message;
use Romm\Formz\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator as ExtbaseAbstractValidator;

/**
 * This validator is used to use some validator functionality without actually
 * creating a validator instance every time it is needed.
 *
 * This is for instance used in `FormzLocalizationJavaScriptAssetHandler` to get
 * translated messages of the messages, to store them in JavaScript.
 */
class DummyValidator extends AbstractValidator implements SingletonInterface
{

    /**
     * Contains all the data (options, messages) of every validator which was
     * cloned by the function `cloneValidator()`.
     */
    protected $validatorsData = [];

    /**
     * @var ReflectionService
     */
    protected $reflectionService;

    /**
     * @var string
     */
    protected $currentValidator;

    /**
     * Will clone the data of the given validator class name. Please note that
     * it will use reflection to get the default value of the class properties.
     *
     * @param string $validatorClassName
     * @return $this
     */
    public function cloneValidator($validatorClassName)
    {
        if (false === isset($this->validatorsData[$validatorClassName])
            && in_array(ExtbaseAbstractValidator::class, class_parents($validatorClassName))
        ) {
            $validatorReflection = new \ReflectionClass($validatorClassName);
            $validatorProperties = $validatorReflection->getDefaultProperties();
            unset($validatorReflection);

            $this->validatorsData[$validatorClassName] = [
                'supportedOptions'   => $validatorProperties['supportedOptions'],
                'acceptsEmptyValues' => $validatorProperties['acceptsEmptyValues']
            ];

            if (in_array(AbstractValidator::class, class_parents($validatorClassName))) {
                $this->validatorsData[$validatorClassName]['supportedMessages'] = $validatorProperties['supportedMessages'];
                $this->validatorsData[$validatorClassName]['supportsAllMessages'] = $validatorProperties['supportsAllMessages'];
            }
        }

        $this->currentValidator = $validatorClassName;

        return $this;
    }

    /**
     * Sets the current messages of this dummy validator to the ones given.
     *
     * @param Message[] $messages
     * @return $this
     */
    public function setExternalMessages(array $messages)
    {
        if ($this->currentValidatorIsValid()) {
            $this->supportsAllMessages = $this->validatorsData[$this->currentValidator]['supportsAllMessages'];
            $this->supportedMessages = $this->validatorsData[$this->currentValidator]['supportedMessages'];
            $this->messages = $this->injectMessages($messages);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function acceptsEmptyValues()
    {
        return $this->validatorsData[$this->currentValidator]['acceptsEmptyValues'];
    }

    /**
     * @return bool
     */
    protected function currentValidatorIsValid()
    {
        return
            null !== $this->currentValidator
            && in_array(AbstractValidator::class, class_parents($this->currentValidator))
        ;
    }

    /**
     * Empty, obviously.
     *
     * @inheritdoc
     */
    public function isValid($value)
    {
    }

    /**
     * @param ReflectionService $reflectionService
     */
    public function injectReflectionService(ReflectionService $reflectionService)
    {
        $this->reflectionService = $reflectionService;
    }
}
