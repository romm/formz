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

namespace Romm\Formz\Condition\Items;

use Romm\Formz\AssetHandler\Html\DataAttributesAssetHandler;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * This condition will match when a field has the given value.
 */
class FieldHasValueCondition extends AbstractConditionItem
{
    const CONDITION_NAME = 'fieldHasValue';

    /**
     * @inheritdoc
     * @var array
     */
    protected static $javaScriptFiles = [
        'EXT:formz/Resources/Public/JavaScript/Conditions/Formz.Condition.FieldHasValue.js'
    ];

    /**
     * @var string
     * @validate NotEmpty
     */
    protected $fieldName;

    /**
     * @var string
     */
    protected $fieldValue;

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @return mixed
     */
    public function getFieldValue()
    {
        return $this->fieldValue;
    }

    /**
     * @inheritdoc
     */
    public function getCssResult()
    {
        if ($this->fieldValue == '') {
            return '[' . DataAttributesAssetHandler::getFieldDataValueKey($this->fieldName) . '="' . $this->fieldValue . '"]';
        } else {
            return '[' . DataAttributesAssetHandler::getFieldDataValueKey($this->fieldName) . '~="' . $this->fieldValue . '"]';
        }
    }

    /**
     * @inheritdoc
     */
    public function getJavaScriptResult()
    {
        return $this->getDefaultJavaScriptCall(
            [
                'fieldName'  => $this->fieldName,
                'fieldValue' => $this->fieldValue
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getPhpResult(PhpConditionDataObject $dataObject)
    {
        $value = ObjectAccess::getProperty($dataObject->getForm(), $this->fieldName);

        return (is_array($value))
            ? (true === in_array($this->fieldValue, $value))
            : ($value == $this->fieldValue);
    }
}
