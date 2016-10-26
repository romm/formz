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

namespace Romm\Formz\AssetHandler\Css;

use Romm\Formz\AssetHandler\AbstractAssetHandler;
use Romm\Formz\Condition\Processor\CssProcessor;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This asset handler generates the CSS code which will automatically hide
 * certain fields of the form, depending on their activation conditions.
 *
 * Two steps are important:
 *  - First, the container of the field is hidden, no matter what.
 *  - Then, for each activation condition that can be reached, a CSS selector is
 *    generated, and used to display the container.
 */
class FieldsActivationCssAssetHandler extends AbstractAssetHandler
{

    /**
     * Main function of this asset handler.
     *
     * @return string
     */
    public function getFieldsActivationCss()
    {
        $cssBlocks = [];
        $formConfiguration = $this->getFormObject()->getConfiguration();

        /** @var CssProcessor $cssProcessor */
        $cssProcessor = GeneralUtility::makeInstance(CssProcessor::class, $this->getFormObject());

        foreach ($formConfiguration->getFields() as $fieldName => $field) {
            $activationConditionTree = $cssProcessor->getFieldActivationConditionTree($field);

            if (null !== $activationConditionTree) {
                $formName = $this->getFormObject()->getName();
                $fieldContainerSelector = $field->getSettings()->getFieldContainerSelector();

                $fullNodeData = [];
                foreach ($activationConditionTree as $node) {
                    $fullNodeData[] = 'form[name="' . $formName . '"]' . $node . ' ' . $fieldContainerSelector;
                }

                $nodesSelector = implode(',' . CRLF, $fullNodeData);

                $cssBlocks[] = $this->getSingleFieldCssBlock($formName, $fieldName, $fieldContainerSelector, $nodesSelector);
            }
        }

        return implode(CRLF, $cssBlocks);
    }

    /**
     * This function is just here to make the class more readable.
     *
     * @param string $formName               Name of the form.
     * @param string $fieldName              Name of the field.
     * @param string $fieldContainerSelector Selector for the field container.
     * @param string $nodesSelector          Nodes used to display the field container.
     * @return string
     */
    protected function getSingleFieldCssBlock($formName, $fieldName, $fieldContainerSelector, $nodesSelector)
    {
        return <<<CSS
/* Hiding the container of the field "$fieldName" by default */
form[name="$formName"] $fieldContainerSelector {
    display: none;
}

/* Showing the container of the field "$fieldName" */
$nodesSelector {
    display: block;
}
CSS;
    }
}
