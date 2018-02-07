<?php
/*
 * 2018 Abdeljabar SAID <abdeljabar.saiid@gmail.com
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
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * This condition will match when a field is filled with any value.
 */
class FieldIsNotEmptyCondition extends AbstractConditionItem
{
    const CONDITION_IDENTIFIER = 'fieldIsNotEmpty';

    /**
     * @inheritdoc
     * @var array
     */
    protected static $javaScriptFiles = [
        'EXT:formz/Resources/Public/JavaScript/Conditions/Formz.Condition.FieldIsNotEmpty.js'
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
        $valueKey = DataAttributesAssetHandler::getFieldDataValueKey($this->fieldName);

        return '[' . $valueKey . ']:not([' . $valueKey . '=""])';
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
        $value = ObjectAccess::getProperty($dataObject->getForm(), $this->fieldName);

        return !empty($value);
    }

    /**
     * Checks the condition configuration/options.
     *
     * If any syntax/configuration error is found, an exception of type
     * `InvalidConditionException` must be thrown.
     *
     * @throws InvalidConditionException
     */
    protected function checkConditionConfiguration()
    {
        $configuration = $this->formObject->getConfiguration();

        if (false === $configuration->hasField($this->fieldName)) {
            throw InvalidConditionException::conditionFieldIsNotEmptyFieldNotFound($this->fieldName);
        }
    }
}
