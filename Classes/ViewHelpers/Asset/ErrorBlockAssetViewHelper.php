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

namespace Romm\Formz\ViewHelpers\Asset;

use Romm\Formz\Service\ExtensionService;
use Romm\Formz\Service\StringService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

class ErrorBlockAssetViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected static $assetIncluded = false;

    /**
     * @inheritdoc
     */
    public function render()
    {
        if (false === self::$assetIncluded) {
            self::$assetIncluded = true;

            $templatePath = GeneralUtility::getFileAbsFileName('EXT:' . ExtensionService::get()->getExtensionKey() . '/Resources/Public/StyleSheets/Form.ErrorBlock.css');

            $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
            $pageRenderer->addCssFile(StringService::get()->getResourceRelativePath($templatePath));
        }
    }
}
