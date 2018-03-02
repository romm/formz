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

use Romm\Formz\Condition\Exceptions\InvalidConditionException;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use Romm\Formz\Form\Definition\FormDefinition;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * This condition will match when a field has a number of selected items between
 * a given minimum and maximum.
 */
class FieldCountValuesCondition extends AbstractConditionItem
{
    const CONDITION_IDENTIFIER = 'fieldCountValues';

    /**
     * @inheritdoc
     * @var array
     */
    protected static $javaScriptFiles = [
        'EXT:formz/Resources/Public/JavaScript/Conditions/Formz.Condition.FieldCountValues.js'
    ];

    /**
     * @var string
     * @validate NotEmpty
     */
    protected $fieldName;

    /**
     * @var int
     */
    protected $minimum;

    /**
     * @var int
     */
    protected $maximum;

    /**
     * @param string $fieldName
     * @param int $minimum
     * @param int $maximum
     */
    public function __construct($fieldName, $minimum = null, $maximum = null)
    {
        $this->fieldName = $fieldName;
        $this->minimum = $minimum;
        $this->maximum = $maximum;
    }

    /**
     * @inheritdoc
     */
    public function getJavaScriptResult()
    {
        return $this->getDefaultJavaScriptCall([
            'fieldName'  => $this->fieldName,
            'minimum' => $this->minimum,
            'maximum' => $this->maximum
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getPhpResult(PhpConditionDataObject $dataObject)
    {
        $value = ObjectAccess::getProperty($dataObject->getForm(), $this->fieldName);

        return !($this->minimum && count($value) < (int)$this->minimum)
            && !($this->maximum && count($value) > (int)$this->maximum);
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidConditionException
     */
    protected function checkConditionConfiguration(FormDefinition $formDefinition)
    {
        if (false === $formDefinition->hasField($this->fieldName)) {
            throw InvalidConditionException::conditionFieldCountValuesFieldNotFound($this->fieldName);
        }
    }

    /**
     * @return string
     */
    public function getCssResult()
    {
        return '';
    }
}
