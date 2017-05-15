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

namespace Romm\Formz\Service;

use Romm\Formz\Form\Definition\Field\Validation\Validator;
use Romm\Formz\Service\Traits\SelfInstantiateTrait;
use Romm\Formz\Validation\Validator\AbstractValidator as FormzAbstractValidator;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class ValidatorService implements SingletonInterface
{
    use SelfInstantiateTrait;

    /**
     * Contains all the data (options, messages) of every validator which was
     * analyzed by the function `getValidatorData()`.
     *
     * @var array
     */
    protected $validatorsData = [];

    /**
     * @param Validator $validator
     * @return bool
     */
    public function validatorAcceptsEmptyValues(Validator $validator)
    {
        $validatorData = $this->getValidatorData($validator);

        return (isset($validatorData['acceptsEmptyValues']))
            ? (bool)$validatorData['acceptsEmptyValues']
            : false;
    }

    /**
     * @param Validator $validator
     * @return array
     */
    public function getValidatorMessages(Validator $validator)
    {
        $validatorData = $this->getValidatorData($validator);
        $messages = (isset($validatorData['formzValidator']))
            ? $this->filterMessages(
                $validator,
                $validatorData['supportedMessages'],
                $validatorData['supportsAllMessages']
            )
            : [];

        return $messages;
    }

    /**
     * Will clone the data of the given validator class name. Please note that
     * it will use reflection to get the default value of the class properties.
     *
     * @param Validator $validator
     * @return array
     */
    protected function getValidatorData(Validator $validator)
    {
        $validatorClassName = $validator->getClassName();

        if (false === isset($this->validatorsData[$validatorClassName])) {
            $this->validatorsData[$validatorClassName] = [];

            if (in_array(AbstractValidator::class, class_parents($validatorClassName))) {
                $validatorReflection = new \ReflectionClass($validatorClassName);
                $validatorProperties = $validatorReflection->getDefaultProperties();
                unset($validatorReflection);

                $validatorData = [
                    'supportedOptions'   => $validatorProperties['supportedOptions'],
                    'acceptsEmptyValues' => $validatorProperties['acceptsEmptyValues']
                ];

                if (in_array(FormzAbstractValidator::class, class_parents($validatorClassName))) {
                    $validatorData['formzValidator'] = true;
                    $validatorData['supportedMessages'] = $validatorProperties['supportedMessages'];
                    $validatorData['supportsAllMessages'] = $validatorProperties['supportsAllMessages'];
                }

                $this->validatorsData[$validatorClassName] = $validatorData;
            }
        }

        return $this->validatorsData[$validatorClassName];
    }

    /**
     * Will return an array by considering the supported messages, and filling
     * the supported ones with the given values.
     *
     * @param Validator $validator
     * @param array     $supportedMessages
     * @param bool      $canCreateNewMessages
     * @return array
     */
    public function filterMessages(Validator $validator, array $supportedMessages, $canCreateNewMessages = false)
    {
        $messagesArray = [];

        foreach ($validator->getMessages() as $message) {
            $messagesArray[$message->getIdentifier()] = $message->toArray();
        }

        $this->addValueToMessage($messagesArray);
        $this->addValueToMessage($supportedMessages);

        $messagesResult = $supportedMessages;

        ArrayUtility::mergeRecursiveWithOverrule(
            $messagesResult,
            $messagesArray,
            (bool)$canCreateNewMessages
        );

        return $messagesResult;
    }

    /**
     * Adding the keys `value` and `extension` to the messages, only if it is
     * missing.
     *
     * @param array $array
     */
    private function addValueToMessage(array &$array)
    {
        foreach ($array as $key => $value) {
            if (false === isset($value['value'])) {
                $array[$key]['value'] = '';
            }

            if (false === isset($value['extension'])) {
                $array[$key]['extension'] = '';
            }
        }
    }
}
