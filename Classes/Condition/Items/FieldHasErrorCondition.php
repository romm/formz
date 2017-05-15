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

namespace Romm\Formz\Condition\Items;

use Romm\Formz\AssetHandler\Html\DataAttributesAssetHandler;
use Romm\Formz\Condition\Exceptions\InvalidConditionException;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use Romm\Formz\Error\FormzMessageInterface;
use Romm\Formz\Form\Definition\FormDefinition;

/**
 * This condition will match when a field is does have a specific error.
 *
 * Note: an error is identified by a name of validation (example "isValid"), and
 * by the name of the error returned by the validator ("default" by default).
 */
class FieldHasErrorCondition extends AbstractConditionItem
{
    const CONDITION_IDENTIFIER = 'fieldHasError';

    /**
     * @inheritdoc
     * @var array
     */
    protected static $javaScriptFiles = [
        'EXT:formz/Resources/Public/JavaScript/Conditions/Formz.Condition.FieldHasError.js'
    ];

    /**
     * @var string
     * @validate NotEmpty
     */
    protected $fieldName;

    /**
     * @var string
     * @validate NotEmpty
     */
    protected $validationName;

    /**
     * @var string
     * @validate NotEmpty
     */
    protected $errorName;

    /**
     * @param string $fieldName
     * @param string $validationName
     * @param string $errorName
     */
    public function __construct($fieldName, $validationName, $errorName = 'default')
    {
        $this->fieldName = $fieldName;
        $this->validationName = $validationName;
        $this->errorName = $errorName;
    }

    /**
     * @inheritdoc
     */
    public function getCssResult()
    {
        return sprintf(
            '[%s="1"]',
            DataAttributesAssetHandler::getFieldDataValidationMessageKey($this->fieldName, 'error', $this->validationName, $this->errorName)
        );
    }

    /**
     * @inheritdoc
     */
    public function getJavaScriptResult()
    {
        return $this->getDefaultJavaScriptCall([
            'fieldName'      => $this->fieldName,
            'validationName' => $this->validationName,
            'errorName'      => $this->errorName
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getPhpResult(PhpConditionDataObject $dataObject)
    {
        $flag = false;
        $formValidator = $dataObject->getFormValidator();
        $field = $this->formObject->getDefinition()->getField($this->fieldName);
        $formValidator->validateField($field);
        $result = $formValidator->getResult()->forProperty($this->fieldName);

        foreach ($result->getErrors() as $error) {
            if ($error instanceof FormzMessageInterface
                && $this->validationName === $error->getValidationName()
                && $this->errorName === $error->getMessageKey()
            ) {
                $flag = true;
                break;
            }
        }

        return $flag;
    }

    /**
     * Checks the condition configuration/options.
     *
     * If any syntax/configuration error is found, an exception of type
     * `InvalidConditionException` must be thrown.
     *
     * @param FormDefinition $formDefinition
     * @throws InvalidConditionException
     */
    protected function checkConditionConfiguration(FormDefinition $formDefinition)
    {
        if (false === $formDefinition->hasField($this->fieldName)) {
            throw InvalidConditionException::conditionFieldHasErrorFieldNotFound($this->fieldName);
        }

        if (false === $formDefinition->getField($this->fieldName)->hasValidator($this->validationName)) {
            throw InvalidConditionException::conditionFieldHasErrorValidationNotFound($this->validationName, $this->fieldName);
        }
    }
}
