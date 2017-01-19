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

namespace Romm\Formz\Condition\Items;

use Romm\Formz\AssetHandler\Html\DataAttributesAssetHandler;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use Romm\Formz\Error\FormzMessageInterface;

/**
 * This condition will match when a field is does have a specific error.
 *
 * Note: an error is identified by a name of validation (example "isValid"), and
 * by the name of the error returned by the validator ("default" by default).
 */
class FieldHasErrorCondition extends AbstractConditionItem
{
    const CONDITION_NAME = 'fieldHasError';

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
    protected $errorName = 'default';

    /**
     * @inheritdoc
     */
    public function getCssResult()
    {
        return sprintf(
            '[%s="1"]',
            DataAttributesAssetHandler::getFieldDataValidationErrorKey($this->fieldName, $this->validationName, $this->errorName)
        );
    }

    /**
     * @inheritdoc
     */
    public function getJavaScriptResult()
    {
        return $this->getDefaultJavaScriptCall(
            [
                'fieldName'      => $this->fieldName,
                'validationName' => $this->validationName,
                'errorName'      => $this->errorName
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getPhpResult(PhpConditionDataObject $dataObject)
    {
        $flag = false;
        $result = $dataObject->getFormValidator()
            ->validateField($this->fieldName)
            ->forProperty($this->fieldName);

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
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @return string
     */
    public function getValidationName()
    {
        return $this->validationName;
    }

    /**
     * @return string
     */
    public function getErrorName()
    {
        return $this->errorName;
    }
}
