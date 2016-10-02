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
use Romm\Formz\AssetHandler\Html\DataAttributesAssetHandler;

/**
 * This asset handler generates the CSS code which will automatically hide the
 * error container of the fields when they have no errors.
 */
class ErrorContainerDisplayCssAssetHandler extends AbstractAssetHandler
{

    /**
     * Main function of this asset handler.
     *
     * @return string
     */
    public function getErrorContainerDisplayCss()
    {
        $cssBlocks = [];

        foreach ($this->getFormConfiguration()->getFields() as $fieldName => $field) {
            $formName = $this->getFormObject()->getName();
            $errorSelector = DataAttributesAssetHandler::getFieldDataErrorKey($fieldName);
            $errorContainerCss = $field->getSettings()->getFeedbackContainerSelector();

            $cssBlocks[] = <<<CSS
form[name="$formName"]:not([$errorSelector="1"]) $errorContainerCss {
    display: none!important;
}
CSS;
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
