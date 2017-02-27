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

namespace Romm\Formz\Service;

use Romm\Formz\Service\Traits\FacadeInstanceTrait;
use Romm\Formz\Validation\Validator\AbstractValidator as FormzAbstractValidator;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class ValidatorService implements SingletonInterface
{
    use FacadeInstanceTrait;

    /**
     * Contains all the data (options, messages) of every validator which was
     * analyzed by the function `getValidatorData()`.
     *
     * @var array
     */
    protected $validatorsData = [];

    /**
     * @param string $validatorClassName
     * @return bool
     */
    public function validatorAcceptsEmptyValues($validatorClassName)
    {
        $validatorData = $this->getValidatorData($validatorClassName);

        return (isset($validatorData['acceptsEmptyValues']))
            ? (bool)$validatorData['acceptsEmptyValues']
            : false;
    }

    /**
     * @param string $validatorClassName
     * @param array  $messages
     * @return array
     */
    public function getValidatorMessages($validatorClassName, array $messages)
    {
        $validatorData = $this->getValidatorData($validatorClassName);
        $messages = (isset($validatorData['formzValidator']))
            ? MessageService::get()->filterMessages(
                $messages,
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
     * @param $validatorClassName
     * @return array
     */
    protected function getValidatorData($validatorClassName)
    {
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
}
