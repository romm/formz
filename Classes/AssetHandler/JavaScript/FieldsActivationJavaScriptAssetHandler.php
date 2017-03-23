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

namespace Romm\Formz\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\AbstractAssetHandler;
use Romm\Formz\Condition\Parser\ConditionTree;
use Romm\Formz\Configuration\Form\Field\Field;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This asset handler generates the JavaScript code used the activate the
 * validation rules of a field. Indeed, when a field is hidden, it is probably
 * not useful to let JavaScript run the validation rules.
 */
class FieldsActivationJavaScriptAssetHandler extends AbstractAssetHandler
{

    /**
     * Main function of this asset handler.
     *
     * @return string
     */
    public function getFieldsActivationJavaScriptCode()
    {
        $javaScriptBlocks = [];
        $formConfiguration = $this->getFormObject()->getConfiguration();

        foreach ($formConfiguration->getFields() as $field) {
            $fieldConditionExpression = [];
            $javaScriptTree = $this->getConditionTreeForField($field)->getJavaScriptConditions();

            if (false === empty($javaScriptTree)) {
                foreach ($javaScriptTree as $node) {
                    $fieldConditionExpression[] = 'flag = flag || (' . $node . ');';
                }

                $javaScriptBlocks[] = $this->getSingleFieldActivationConditionFunction($field, $fieldConditionExpression);
            }
        }

        $javaScriptBlocks = implode(CRLF, $javaScriptBlocks);
        $formName = GeneralUtility::quoteJSvalue($this->getFormObject()->getName());

        return <<<JS
(function() {
    Fz.Form.get(
        $formName,
        function(form) {
            var field = null;

$javaScriptBlocks
        }
    );
})();
JS;
    }

    /**
     * This function is just here to make the class more readable.
     *
     * @param Field $field                    Field instance.
     * @param array $fieldConditionExpression Array containing the JavaScript condition expression for the field.
     * @return string
     */
    protected function getSingleFieldActivationConditionFunction(Field $field, $fieldConditionExpression)
    {
        $fieldName = GeneralUtility::quoteJSvalue($field->getName());
        $fieldConditionExpression = implode(CRLF . str_repeat(' ', 20), $fieldConditionExpression);

        return <<<JS
            field = form.getFieldByName($fieldName);

            if (null !== field) {
                field.addActivationCondition(
                    '__auto',
                    function (field, continueValidation) {
                        var flag = false;
                        $fieldConditionExpression
                        continueValidation(flag);
                    }
                );
            }
JS;
    }

    /**
     * @param Field $field
     * @return ConditionTree
     */
    protected function getConditionTreeForField(Field $field)
    {
        return $this->conditionProcessor->getActivationConditionTreeForField($field);
    }
}
