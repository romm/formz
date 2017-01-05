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

namespace Romm\Formz\AssetHandler\Connector;

use Romm\Formz\AssetHandler\Css\ErrorContainerDisplayCssAssetHandler;
use Romm\Formz\AssetHandler\Css\FieldsActivationCssAssetHandler;
use Romm\Formz\Core\Core;

class CssAssetHandlerConnector
{
    /**
     * List of CSS files which will be included whenever this view helper is
     * used.
     *
     * @var array
     */
    private $cssFiles = [
        'Form.Main.css'
    ];

    /**
     * @var AssetHandlerConnectorManager
     */
    private $assetHandlerConnectorManager;

    /**
     * @param AssetHandlerConnectorManager $assetHandlerConnectorManager
     */
    public function __construct(AssetHandlerConnectorManager $assetHandlerConnectorManager)
    {
        $this->assetHandlerConnectorManager = $assetHandlerConnectorManager;
    }

    /**
     * Will include all default CSS files declared in the property `$cssFiles`
     * of this class.
     *
     * @return $this
     */
    public function includeDefaultCssFiles()
    {
        foreach ($this->cssFiles as $file) {
            $filePath = Core::get()->getExtensionRelativePath('Resources/Public/StyleSheets/' . $file);

            $this->assetHandlerConnectorManager
                ->getPageRenderer()
                ->addCssFile($filePath);
        }

        return $this;
    }

    /**
     * Will take care of generating the CSS with the `AssetHandlerFactory`. The
     * code will be put in a `.css` file in the `typo3temp` directory.
     *
     * If the file already exists, it is included directly before the code
     * generation.
     *
     * @return $this
     */
    public function includeGeneratedCss()
    {
        $filePath = $this->assetHandlerConnectorManager->getFormzGeneratedFilePath() . '.css';

        $this->assetHandlerConnectorManager->createFileInTemporaryDirectory(
            $filePath,
            function () {
                /** @var ErrorContainerDisplayCssAssetHandler $errorContainerDisplayCssAssetHandler */
                $errorContainerDisplayCssAssetHandler = $this->assetHandlerConnectorManager
                    ->getAssetHandlerFactory()
                    ->getAssetHandler(ErrorContainerDisplayCssAssetHandler::class);

                /** @var FieldsActivationCssAssetHandler $fieldsActivationCssAssetHandler */
                $fieldsActivationCssAssetHandler = $this->assetHandlerConnectorManager
                    ->getAssetHandlerFactory()
                    ->getAssetHandler(FieldsActivationCssAssetHandler::class);

                $css = $errorContainerDisplayCssAssetHandler->getErrorContainerDisplayCss() . LF;
                $css .= $fieldsActivationCssAssetHandler->getFieldsActivationCss();

                return $css;
            }
        );

        $this->assetHandlerConnectorManager
            ->getPageRenderer()
            ->addCssFile($filePath);

        return $this;
    }
}
