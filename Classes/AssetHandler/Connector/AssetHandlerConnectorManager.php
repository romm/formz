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

namespace Romm\Formz\AssetHandler\Connector;

use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\Core\Core;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This manager is used to create instances of connectors, which will be used to
 * gather every asset used for a form, mainly JavaScript and CSS code.
 */
class AssetHandlerConnectorManager
{
    /**
     * @var AssetHandlerConnectorManager[]
     */
    private static $instances = [];

    /**
     * @var PageRenderer
     */
    private $pageRenderer;

    /**
     * @var AssetHandlerFactory
     */
    private $assetHandlerFactory;

    /**
     * @var JavaScriptAssetHandlerConnector
     */
    private $javaScriptAssetHandlerConnector;

    /**
     * @var CssAssetHandlerConnector
     */
    private $cssAssetHandlerConnector;

    /**
     * @var AssetHandlerConnectorStates
     */
    private $assetHandlerConnectorStates;

    /**
     * @param PageRenderer        $pageRenderer
     * @param AssetHandlerFactory $assetHandlerFactory
     */
    public function __construct(PageRenderer $pageRenderer, AssetHandlerFactory $assetHandlerFactory)
    {
        $this->pageRenderer = $pageRenderer;
        $this->assetHandlerFactory = $assetHandlerFactory;

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->cssAssetHandlerConnector = Core::get()
            ->getObjectManager()
            ->get(CssAssetHandlerConnector::class, $this);

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->javaScriptAssetHandlerConnector = Core::get()
            ->getObjectManager()
            ->get(JavaScriptAssetHandlerConnector::class, $this);
    }

    /**
     * @param PageRenderer        $pageRenderer
     * @param AssetHandlerFactory $assetHandlerFactory
     * @return AssetHandlerConnectorManager
     */
    public static function get(PageRenderer $pageRenderer, AssetHandlerFactory $assetHandlerFactory)
    {
        $hash = sha1(spl_object_hash($pageRenderer) . spl_object_hash($assetHandlerFactory));

        if (false === isset(self::$instances[$hash])) {
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            self::$instances[$hash] = Core::get()
                ->getObjectManager()
                ->get(self::class, $pageRenderer, $assetHandlerFactory);
        }

        return self::$instances[$hash];
    }

    /**
     * Will take care of including internal Formz JavaScript and CSS files. They
     * will be included only once, even if the view helper is used several times
     * in the same page.
     *
     * @return $this
     */
    public function includeDefaultAssets()
    {
        if (false === $this->assetHandlerConnectorStates->defaultAssetsWereIncluded()) {
            $this->assetHandlerConnectorStates->markDefaultAssetsAsIncluded();

            $this->getJavaScriptAssetHandlerConnector()->includeDefaultJavaScriptFiles();
            $this->getCssAssetHandlerConnector()->includeDefaultCssFiles();
        }

        return $this;
    }

    /**
     * Returns a file name based on the form object class name.
     *
     * @param string $prefix
     * @return string
     */
    public function getFormzGeneratedFilePath($prefix = '')
    {
        $formObject = $this->assetHandlerFactory->getFormObject();
        $prefix = (false === empty($prefix))
            ? $prefix . '-'
            : '';

        return Core::GENERATED_FILES_PATH . Core::get()->getCacheIdentifier('formz-' . $prefix, $formObject->getClassName() . '-' . $formObject->getName());
    }

    /**
     * This function will check if the file at the given path exists. If it does
     * not, the callback is called to get the content of the file, which is put
     * in the created file.
     *
     * A boolean is returned: if the file did not exist, and it was created
     * without error, `true` is returned. Otherwise, `false` is returned.
     *
     * @param string   $relativePath
     * @param callable $callback
     * @return bool
     */
    public function createFileInTemporaryDirectory($relativePath, callable $callback)
    {
        $result = false;
        $absolutePath = GeneralUtility::getFileAbsFileName($relativePath);

        if (false === $this->fileExists($absolutePath)) {
            $content = call_user_func($callback);

            $result = $this->writeTemporaryFile($absolutePath, $content);
        }

        return $result;
    }

    /**
     * This function is mocked in unit tests.
     *
     * @param string $absolutePath
     * @return bool
     */
    protected function fileExists($absolutePath)
    {
        return file_exists($absolutePath);
    }

    /**
     * This function is mocked in unit tests.
     *
     * @param string $absolutePath
     * @param string $content
     * @return bool
     */
    protected function writeTemporaryFile($absolutePath, $content)
    {
        $result = GeneralUtility::writeFileToTypo3tempDir($absolutePath, $content);

        return (null === $result);
    }

    /**
     * @return PageRenderer
     */
    public function getPageRenderer()
    {
        return $this->pageRenderer;
    }

    /**
     * @return AssetHandlerFactory
     */
    public function getAssetHandlerFactory()
    {
        return $this->assetHandlerFactory;
    }

    /**
     * @return AssetHandlerConnectorStates
     */
    public function getAssetHandlerConnectorStates()
    {
        return $this->assetHandlerConnectorStates;
    }

    /**
     * @return JavaScriptAssetHandlerConnector
     */
    public function getJavaScriptAssetHandlerConnector()
    {
        return $this->javaScriptAssetHandlerConnector;
    }

    /**
     * @return CssAssetHandlerConnector
     */
    public function getCssAssetHandlerConnector()
    {
        return $this->cssAssetHandlerConnector;
    }

    /**
     * @param AssetHandlerConnectorStates $assetHandlerConnectorStates
     */
    public function injectAssetHandlerConnectorStates(AssetHandlerConnectorStates $assetHandlerConnectorStates)
    {
        $this->assetHandlerConnectorStates = $assetHandlerConnectorStates;
    }
}
