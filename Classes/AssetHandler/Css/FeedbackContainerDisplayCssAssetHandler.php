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

namespace Romm\Formz\AssetHandler\Css;

use Romm\Formz\AssetHandler\AbstractAssetHandler;
use Romm\Formz\AssetHandler\Html\DataAttributesAssetHandler;

/**
 * This asset handler generates the CSS code which will automatically hide the
 * error container of the fields when they have no errors.
 */
class FeedbackContainerDisplayCssAssetHandler extends AbstractAssetHandler
{

    /**
     * Main function of this asset handler.
     *
     * @return string
     */
    public function getErrorContainerDisplayCss()
    {
        $cssBlocks = [];
        $formConfiguration = $this->getFormObject()->getConfiguration();

        foreach ($formConfiguration->getFields() as $fieldName => $field) {
            $formName = $this->getFormObject()->getName();
            $errorSelector = DataAttributesAssetHandler::getFieldDataMessageKey($fieldName, 'error');
            $warningSelector = DataAttributesAssetHandler::getFieldDataMessageKey($fieldName, 'warning');
            $noticeSelector = DataAttributesAssetHandler::getFieldDataMessageKey($fieldName, 'notice');
            $errorContainerCss = $field->getSettings()->getFeedbackContainerSelector();

            $cssBlocks[] = <<<CSS
form[name="$formName"]:not([$errorSelector="1"]):not([$warningSelector="1"]):not([$noticeSelector="1"]) $errorContainerCss {
    display: none!important;
}
CSS;
        }

        return implode(CRLF, $cssBlocks);
    }
}
