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
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Validation\Validator\Form\AbstractFormValidator;

/**
 * This condition will match when a field is valid (its validation returned no
 * error).
 */
class FieldIsValidCondition extends AbstractConditionItem
{

    const CONDITION_NAME = 'fieldIsValid';

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
    public function getPhpResult(FormInterface $form, AbstractFormValidator $formValidator)
    {
        $result = $formValidator->validateField($this->fieldName);

        return (
            false === $result->forProperty($this->fieldName)->hasErrors()
            && null === $result->getData(AbstractFormValidator::RESULT_KEY_ACTIVATION_PROPERTY . '.' . $this->fieldName)
        );
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @param string $fieldName
     * @return $this
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;

        return $this;
    }
}
