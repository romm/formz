<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\Connector;

use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\AssetHandler\Connector\AssetHandlerConnectorFactory;
use Romm\Formz\AssetHandler\Connector\AssetHandlerConnectorStates;
use Romm\Formz\AssetHandler\Connector\CssAssetHandlerConnector;
use Romm\Formz\AssetHandler\Connector\JavaScriptAssetHandlerConnector;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;

class AssetHandlerConnectorFactoryTest extends AbstractUnitTest
{

    /**
     * Checks that the factory getter method returns correct instances, and that
     * there wont be duplicated instances of factories for the same constructor
     * arguments.
     *
     * @test
     */
    public function factoryGetterMethodWorksWell()
    {
        $formObject = $this->getFormObject();
        $controllerContext = new ControllerContext;

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);
        $pageRenderer = new PageRenderer;

        $assetHandlerConnectorFactory = AssetHandlerConnectorFactory::get($pageRenderer, $assetHandlerFactory);

        $this->assertInstanceOf(AssetHandlerConnectorFactory::class, $assetHandlerConnectorFactory);
        $this->assertSame($pageRenderer, $assetHandlerConnectorFactory->getPageRenderer());
        $this->assertSame($assetHandlerFactory, $assetHandlerConnectorFactory->getAssetHandlerFactory());

        // Getting a factory with the same objects should return the same instance.
        $assetHandlerConnectorFactory2 = AssetHandlerConnectorFactory::get($pageRenderer, $assetHandlerFactory);

        $this->assertSame($assetHandlerConnectorFactory, $assetHandlerConnectorFactory2);

        // Getting a factory with different objects should return another instance.
        $pageRenderer2 = new PageRenderer;
        $assetHandlerConnectorFactory3 = AssetHandlerConnectorFactory::get($pageRenderer2, $assetHandlerFactory);

        $this->assertNotSame($assetHandlerConnectorFactory, $assetHandlerConnectorFactory3);
    }

    /**
     * Checks that the function `includeDefaultAssets` will include the assets
     * only once.
     *
     * @test
     */
    public function includingDefaultAssetsIncludesThemOnce()
    {
        $formObject = $this->getFormObject();
        $controllerContext = new ControllerContext;

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);
        $pageRenderer = new PageRenderer;

        /** @var AssetHandlerConnectorFactory|\PHPUnit_Framework_MockObject_MockObject $assetHandlerConnectorFactoryMock */
        $assetHandlerConnectorFactoryMock = $this->getMock(
            AssetHandlerConnectorFactory::class,
            ['getJavaScriptAssetHandlerConnector', 'getCssAssetHandlerConnector'],
            [$pageRenderer, $assetHandlerFactory]
        );

        $assetHandlerConnectorStates = new AssetHandlerConnectorStates;
        $assetHandlerConnectorFactoryMock->injectAssetHandlerConnectorStates($assetHandlerConnectorStates);
        $this->assertSame($assetHandlerConnectorStates, $assetHandlerConnectorFactoryMock->getAssetHandlerConnectorStates());

        /** @var JavaScriptAssetHandlerConnector|\PHPUnit_Framework_MockObject_MockObject $javaScriptAssetHandlerConnectorMock */
        $javaScriptAssetHandlerConnectorMock = $this->getMock(
            JavaScriptAssetHandlerConnector::class,
            ['includeDefaultJavaScriptFiles'],
            [$assetHandlerConnectorFactoryMock]
        );
        $javaScriptAssetHandlerConnectorMock->expects($this->once())
            ->method('includeDefaultJavaScriptFiles');

        $assetHandlerConnectorFactoryMock->method('getJavaScriptAssetHandlerConnector')
            ->willReturn($javaScriptAssetHandlerConnectorMock);

        /** @var CssAssetHandlerConnector|\PHPUnit_Framework_MockObject_MockObject $cssAssetHandlerConnectorMock */
        $cssAssetHandlerConnectorMock = $this->getMock(
            CssAssetHandlerConnector::class,
            ['includeDefaultCssFiles'],
            [$assetHandlerConnectorFactoryMock]
        );
        $cssAssetHandlerConnectorMock->expects($this->once())
            ->method('includeDefaultCssFiles');

        $assetHandlerConnectorFactoryMock->method('getCssAssetHandlerConnector')
            ->willReturn($cssAssetHandlerConnectorMock);

        $assetHandlerConnectorFactoryMock->includeDefaultAssets();
        $assetHandlerConnectorFactoryMock->includeDefaultAssets();
        $assetHandlerConnectorFactoryMock->includeDefaultAssets();
    }

    /**
     * Generated file path should be different for two different prefixes.
     *
     * @test
     */
    public function GeneratedFilePathDependsOnPrefix()
    {
        $formObject = $this->getFormObject();
        $controllerContext = new ControllerContext;

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);
        $pageRenderer = new PageRenderer;

        $assetHandlerConnectorFactory = new AssetHandlerConnectorFactory($pageRenderer, $assetHandlerFactory);

        $path1 = $assetHandlerConnectorFactory->getFormzGeneratedFilePath();
        $path2 = $assetHandlerConnectorFactory->getFormzGeneratedFilePath();
        $this->assertEquals($path1, $path2);

        $path3 = $assetHandlerConnectorFactory->getFormzGeneratedFilePath('foo');
        $path4 = $assetHandlerConnectorFactory->getFormzGeneratedFilePath('foo');
        $this->assertEquals($path3, $path4);
        $this->assertNotEquals($path1, $path3);
    }
}
