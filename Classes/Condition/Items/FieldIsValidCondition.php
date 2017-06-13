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
use Romm\Formz\Form\Definition\FormDefinition;

/**
 * This condition will match when a field is valid (its validation returned no
 * error).
 */
class FieldIsValidCondition extends AbstractConditionItem
{
    const CONDITION_IDENTIFIER = 'fieldIsValid';

    /**
     * @inheritdoc
     * @var array
     */
    protected static $javaScriptFiles = [
        'EXT:formz/Resources/Public/JavaScript/Conditions/Formz.Condition.FieldIsValid.js'
    ];

    /**
     * @var string
     * @validate NotEmpty
     */
    protected $fieldName;

    /**
     * @param string $fieldName
     */
    public function __construct($fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * @inheritdoc
     */
    public function getCssResult()
    {
        return '[' . DataAttributesAssetHandler::getFieldDataValidKey($this->fieldName) . '="1"]';
    }

    /**
     * @inheritdoc
     */
    public function getJavaScriptResult()
    {
        return $this->getDefaultJavaScriptCall(['fieldName' => $this->fieldName]);
    }

    /**
     * @inheritdoc
     */
    public function getPhpResult(PhpConditionDataObject $dataObject)
    {
        $formValidator = $dataObject->getFormValidator();
        $field = $this->formObject->getDefinition()->getField($this->fieldName);
        $formValidator->validateField($field);
        $result = $formValidator->getResult();

        return false === $result->forProperty($this->fieldName)->hasErrors()
            && false === $result->fieldIsOutOfScope($field)
            && false === $result->fieldIsDeactivated($field);
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
            throw InvalidConditionException::conditionFieldIsValidFieldNotFound($this->fieldName);
        }
    }
}
