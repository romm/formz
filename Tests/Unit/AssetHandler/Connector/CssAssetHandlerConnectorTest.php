<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\Connector;

use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\AssetHandler\Connector\AssetHandlerConnectorManager;
use Romm\Formz\AssetHandler\Connector\CssAssetHandlerConnector;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;

class CssAssetHandlerConnectorTest extends AbstractUnitTest
{
    /**
     * Checks that the default CSS files are included with the page renderer.
     *
     * @test
     */
    public function defaultCssFilesAreIncluded()
    {
        $formObject = $this->getDefaultFormObject();
        $controllerContext = new ControllerContext;

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);

        /** @var PageRenderer|\PHPUnit_Framework_MockObject_MockObject $pageRendererMock */
        $pageRendererMock = $this->getMockBuilder(PageRenderer::class)
            ->setMethods(['addCssFile'])
            ->getMock();
        $pageRendererMock->expects($this->atLeastOnce())
            ->method('addCssFile');

        $assetHandlerConnectorManager = new AssetHandlerConnectorManager($pageRendererMock, $assetHandlerFactory);

        $cssAssetHandlerConnector = new CssAssetHandlerConnector($assetHandlerConnectorManager);

        $return = $cssAssetHandlerConnector->includeDefaultCssFiles();

        // Checking that the function returns `$this`.
        $this->assertSame($return, $cssAssetHandlerConnector);
    }

    /**
     * Will check that the CSS is generated only once, then put in a file that
     * is included in the page renderer.
     *
     * @test
     */
    public function generatedCssAreGeneratedOnceAndIncluded()
    {
        $formObject = $this->getDefaultFormObject();
        $controllerContext = new ControllerContext;

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);

        $pageRendererMock = $this->getMockBuilder(PageRenderer::class)
            ->setMethods(['addCssFile'])
            ->getMock();
        $pageRendererMock->expects($this->atLeastOnce())
            ->method('addCssFile');

        /** @var AssetHandlerConnectorManager|\PHPUnit_Framework_MockObject_MockObject $assetHandlerConnectorManager */
        $assetHandlerConnectorManager = $this->getMockBuilder(AssetHandlerConnectorManager::class)
            ->setMethods(['fileExists', 'writeTemporaryFile'])
            ->setConstructorArgs([$pageRendererMock, $assetHandlerFactory])
            ->getMock();

        $fileExists = false;
        $assetHandlerConnectorManager->method('fileExists')
            ->willReturnCallback(function () use (&$fileExists) {
                $result = $fileExists;
                $fileExists = true;

                return $result;
            });

        $assetHandlerConnectorManager->expects($this->once())
            ->method('writeTemporaryFile')
            ->willReturn(true);

        $cssAssetHandlerConnector = new CssAssetHandlerConnector($assetHandlerConnectorManager);

        $cssAssetHandlerConnector->includeGeneratedCss();
        $cssAssetHandlerConnector->includeGeneratedCss();
        $return = $cssAssetHandlerConnector->includeGeneratedCss();

        // Checking that the function returns `$this`.
        $this->assertSame($return, $cssAssetHandlerConnector);
    }
}
