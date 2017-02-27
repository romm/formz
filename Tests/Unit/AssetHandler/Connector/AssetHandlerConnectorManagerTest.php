<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\Connector;

use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\AssetHandler\Connector\AssetHandlerConnectorManager;
use Romm\Formz\AssetHandler\Connector\AssetHandlerConnectorStates;
use Romm\Formz\AssetHandler\Connector\CssAssetHandlerConnector;
use Romm\Formz\AssetHandler\Connector\JavaScriptAssetHandlerConnector;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;

class AssetHandlerConnectorManagerTest extends AbstractUnitTest
{

    /**
     * Checks that the manager getter method returns correct instances, and that
     * there wont be duplicated instances for the same constructor arguments.
     *
     * @test
     */
    public function instanceGetterMethodWorksWell()
    {
        $formObject = $this->getDefaultFormObject();
        $controllerContext = new ControllerContext;

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);
        $pageRenderer = new PageRenderer;

        $assetHandlerConnectorManager = AssetHandlerConnectorManager::get($pageRenderer, $assetHandlerFactory);

        $this->assertInstanceOf(AssetHandlerConnectorManager::class, $assetHandlerConnectorManager);
        $this->assertSame($pageRenderer, $assetHandlerConnectorManager->getPageRenderer());
        $this->assertSame($assetHandlerFactory, $assetHandlerConnectorManager->getAssetHandlerFactory());

        // Getting a factory with the same objects should return the same instance.
        $assetHandlerConnectorManager2 = AssetHandlerConnectorManager::get($pageRenderer, $assetHandlerFactory);

        $this->assertSame($assetHandlerConnectorManager, $assetHandlerConnectorManager2);

        // Getting a factory with different objects should return another instance.
        $pageRenderer2 = new PageRenderer;
        $assetHandlerConnectorManager3 = AssetHandlerConnectorManager::get($pageRenderer2, $assetHandlerFactory);

        $this->assertNotSame($assetHandlerConnectorManager, $assetHandlerConnectorManager3);
    }

    /**
     * Checks that the function `includeDefaultAssets` will include the assets
     * only once.
     *
     * @test
     */
    public function includingDefaultAssetsIncludesThemOnce()
    {
        $formObject = $this->getDefaultFormObject();
        $controllerContext = new ControllerContext;

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);
        $pageRenderer = new PageRenderer;

        /** @var AssetHandlerConnectorManager|\PHPUnit_Framework_MockObject_MockObject $assetHandlerConnectorManagerMock */
        $assetHandlerConnectorManagerMock = $this->getMockBuilder(AssetHandlerConnectorManager::class)
            ->setMethods(['getJavaScriptAssetHandlerConnector', 'getCssAssetHandlerConnector'])
            ->setConstructorArgs([$pageRenderer, $assetHandlerFactory])
            ->getMock();

        $assetHandlerConnectorStates = new AssetHandlerConnectorStates;
        $assetHandlerConnectorManagerMock->injectAssetHandlerConnectorStates($assetHandlerConnectorStates);
        $this->assertSame($assetHandlerConnectorStates, $assetHandlerConnectorManagerMock->getAssetHandlerConnectorStates());

        /** @var JavaScriptAssetHandlerConnector|\PHPUnit_Framework_MockObject_MockObject $javaScriptAssetHandlerConnectorMock */
        $javaScriptAssetHandlerConnectorMock = $this->getMockBuilder(JavaScriptAssetHandlerConnector::class)
            ->setMethods(['includeDefaultJavaScriptFiles'])
            ->setConstructorArgs([$assetHandlerConnectorManagerMock])
            ->getMock();
        $javaScriptAssetHandlerConnectorMock->expects($this->once())
            ->method('includeDefaultJavaScriptFiles');

        $assetHandlerConnectorManagerMock->method('getJavaScriptAssetHandlerConnector')
            ->willReturn($javaScriptAssetHandlerConnectorMock);

        /** @var CssAssetHandlerConnector|\PHPUnit_Framework_MockObject_MockObject $cssAssetHandlerConnectorMock */
        $cssAssetHandlerConnectorMock = $this->getMockBuilder(CssAssetHandlerConnector::class)
            ->setMethods(['includeDefaultCssFiles'])
            ->setConstructorArgs([$assetHandlerConnectorManagerMock])
            ->getMock();
        $cssAssetHandlerConnectorMock->expects($this->once())
            ->method('includeDefaultCssFiles');

        $assetHandlerConnectorManagerMock->method('getCssAssetHandlerConnector')
            ->willReturn($cssAssetHandlerConnectorMock);

        $assetHandlerConnectorManagerMock->includeDefaultAssets();
        $assetHandlerConnectorManagerMock->includeDefaultAssets();
        $assetHandlerConnectorManagerMock->includeDefaultAssets();
    }

    /**
     * Generated file path should be different for two different prefixes.
     *
     * @test
     */
    public function GeneratedFilePathDependsOnPrefix()
    {
        $formObject = $this->getDefaultFormObject();
        $controllerContext = new ControllerContext;

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);
        $pageRenderer = new PageRenderer;

        $assetHandlerConnectorManager = new AssetHandlerConnectorManager($pageRenderer, $assetHandlerFactory);

        $path1 = $assetHandlerConnectorManager->getFormzGeneratedFilePath();
        $path2 = $assetHandlerConnectorManager->getFormzGeneratedFilePath();
        $this->assertEquals($path1, $path2);

        $path3 = $assetHandlerConnectorManager->getFormzGeneratedFilePath('foo');
        $path4 = $assetHandlerConnectorManager->getFormzGeneratedFilePath('foo');
        $this->assertEquals($path3, $path4);
        $this->assertNotEquals($path1, $path3);
    }
}
