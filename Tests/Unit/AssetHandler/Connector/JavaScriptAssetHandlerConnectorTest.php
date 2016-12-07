<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\Connector;

use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\AssetHandler\Connector\AssetHandlerConnectorManager;
use Romm\Formz\AssetHandler\Connector\JavaScriptAssetHandlerConnector;
use Romm\Formz\AssetHandler\JavaScript\FieldsActivationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FieldsValidationActivationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FieldsValidationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FormInitializationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FormzConfigurationJavaScriptAssetHandler;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;

class JavaScriptAssetHandlerConnectorTest extends AbstractUnitTest
{
    /**
     * Checks that the default JavaScript files are included with the page
     * renderer.
     *
     * @test
     */
    public function defaultJavaScriptFilesAreIncluded()
    {
        $this->setExtensionConfigurationValue('debugMode', false);

        $filesIncluded = 0;

        $formObject = $this->getFormObject();
        $controllerContext = new ControllerContext;

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);

        /** @var PageRenderer|\PHPUnit_Framework_MockObject_MockObject $pageRendererMock */
        $pageRendererMock = $this->getMock(PageRenderer::class, ['addJsFile']);
        $pageRendererMock->expects($this->atLeastOnce())
            ->method('addJsFile')
            ->willReturnCallback(function () use (&$filesIncluded) {
                $filesIncluded++;
            });

        $assetHandlerConnectorManager = new AssetHandlerConnectorManager($pageRendererMock, $assetHandlerFactory);
        $javaScriptAssetHandlerConnector = new JavaScriptAssetHandlerConnector($assetHandlerConnectorManager);
        $return = $javaScriptAssetHandlerConnector->includeDefaultJavaScriptFiles();

        // Checking that the function returns `$this`.
        $this->assertSame($return, $javaScriptAssetHandlerConnector);

        /*
         * Checking the same in debug mode: an additional file should be
         * included.
         */
        $this->setExtensionConfigurationValue('debugMode', true);

        $filesIncludedBis = 0;

        /** @var PageRenderer|\PHPUnit_Framework_MockObject_MockObject $pageRendererMockBis */
        $pageRendererMockBis = $this->getMock(PageRenderer::class, ['addJsFile']);
        $pageRendererMockBis->expects($this->atLeastOnce())
            ->method('addJsFile')
            ->willReturnCallback(function () use (&$filesIncludedBis) {
                $filesIncludedBis++;
            });

        $assetHandlerConnectorManagerBis = new AssetHandlerConnectorManager($pageRendererMockBis, $assetHandlerFactory);
        $javaScriptAssetHandlerConnectorBis = new JavaScriptAssetHandlerConnector($assetHandlerConnectorManagerBis);
        $javaScriptAssetHandlerConnectorBis->includeDefaultJavaScriptFiles();

        $this->assertGreaterThan($filesIncluded, $filesIncludedBis);
    }

    /**
     * Checks that the Formz JavaScript configuration is correctly included with
     * the page renderer.
     *
     * @test
     */
    public function formzConfigurationIsGeneratedAndIncluded()
    {
        $formObject = $this->getFormObject();
        $controllerContext = new ControllerContext;

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);

        /** @var PageRenderer|\PHPUnit_Framework_MockObject_MockObject $pageRendererMock */
        $pageRendererMock = $this->getMock(PageRenderer::class, ['addJsFooterFile']);
        $pageRendererMock->expects($this->once())
            ->method('addJsFooterFile');

        $assetHandlerConnectorManager = new AssetHandlerConnectorManager($pageRendererMock, $assetHandlerFactory);

        /** @var JavaScriptAssetHandlerConnector|\PHPUnit_Framework_MockObject_MockObject $javaScriptAssetHandlerConnector */
        $javaScriptAssetHandlerConnector = $this->getMock(
            JavaScriptAssetHandlerConnector::class,
            ['getFormzConfigurationJavaScriptAssetHandler'],
            [$assetHandlerConnectorManager]
        );

        /** @var FormzConfigurationJavaScriptAssetHandler|\PHPUnit_Framework_MockObject_MockObject $formzConfigurationJavaScriptAssetHandlerMock */
        $formzConfigurationJavaScriptAssetHandlerMock = $this->getMock(
            FormzConfigurationJavaScriptAssetHandler::class,
            ['getJavaScriptFileName', 'getJavaScriptCode'],
            [$assetHandlerFactory]
        );

        $formzConfigurationJavaScriptAssetHandlerMock
            ->method('getJavaScriptFileName')
            ->willReturn('foo');

        $formzConfigurationJavaScriptAssetHandlerMock
            ->expects($this->once())
            ->method('getJavaScriptCode')
            ->willReturn('foo');

        $javaScriptAssetHandlerConnector
            ->method('getFormzConfigurationJavaScriptAssetHandler')
            ->willReturn($formzConfigurationJavaScriptAssetHandlerMock);

        $javaScriptAssetHandlerConnector->generateAndIncludeFormzConfigurationJavaScript();
    }

    /**
     * Checks that every single one of the JavaScript general code is generated
     * and included.
     *
     * @test
     */
    public function generalJavaScriptIsGeneratedAndIncluded()
    {
        $formObject = $this->getFormObject();
        $controllerContext = new ControllerContext;

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);

        /** @var PageRenderer|\PHPUnit_Framework_MockObject_MockObject $pageRendererMock */
        $pageRendererMock = $this->getMock(PageRenderer::class, ['addJsFooterFile']);
        $pageRendererMock->expects($this->once())
            ->method('addJsFooterFile');

        /** @var AssetHandlerConnectorManager|\PHPUnit_Framework_MockObject_MockObject $assetHandlerConnectorManagerMock */
        $assetHandlerConnectorManagerMock = $this->getMock(
            AssetHandlerConnectorManager::class,
            ['fileExists', 'writeTemporaryFile'],
            [$pageRendererMock, $assetHandlerFactory]
        );

        $assetHandlerConnectorManagerMock
            ->method('fileExists')
            ->willReturn(false);

        $assetHandlerConnectorManagerMock
            ->method('writeTemporaryFile')
            ->willReturn(true);

        /** @var JavaScriptAssetHandlerConnector|\PHPUnit_Framework_MockObject_MockObject $javaScriptAssetHandlerConnector */
        $javaScriptAssetHandlerConnector = $this->getMock(
            JavaScriptAssetHandlerConnector::class,
            [
                'getFormInitializationJavaScriptAssetHandler',
                'getFieldsValidationJavaScriptAssetHandler',
                'getFieldsActivationJavaScriptAssetHandler',
                'getFieldsValidationActivationJavaScriptAssetHandler'
            ],
            [$assetHandlerConnectorManagerMock]
        );

        /** @var FormInitializationJavaScriptAssetHandler|\PHPUnit_Framework_MockObject_MockObject $formInitializationJavaScriptAssetHandlerMock */
        $formInitializationJavaScriptAssetHandlerMock = $this->getMock(
            FormInitializationJavaScriptAssetHandler::class,
            ['getFormInitializationJavaScriptCode'],
            [$assetHandlerFactory]
        );

        $formInitializationJavaScriptAssetHandlerMock
            ->expects($this->once())
            ->method('getFormInitializationJavaScriptCode')
            ->willReturn('foo');

        $javaScriptAssetHandlerConnector
            ->method('getFormInitializationJavaScriptAssetHandler')
            ->willReturn($formInitializationJavaScriptAssetHandlerMock);

        /** @var FieldsValidationJavaScriptAssetHandler|\PHPUnit_Framework_MockObject_MockObject $formInitializationJavaScriptAssetHandlerMock */
        $fieldsValidationJavaScriptAssetHandlerMock = $this->getMock(
            FieldsValidationJavaScriptAssetHandler::class,
            ['getJavaScriptCode'],
            [$assetHandlerFactory]
        );

        $fieldsValidationJavaScriptAssetHandlerMock
            ->expects($this->once())
            ->method('getJavaScriptCode')
            ->willReturn('foo');

        $javaScriptAssetHandlerConnector
            ->method('getFieldsValidationJavaScriptAssetHandler')
            ->willReturn($fieldsValidationJavaScriptAssetHandlerMock);

        /** @var FieldsActivationJavaScriptAssetHandler|\PHPUnit_Framework_MockObject_MockObject $formInitializationJavaScriptAssetHandlerMock */
        $fieldsActivationJavaScriptAssetHandlerMock = $this->getMock(
            FieldsActivationJavaScriptAssetHandler::class,
            ['getFieldsActivationJavaScriptCode'],
            [$assetHandlerFactory]
        );

        $fieldsActivationJavaScriptAssetHandlerMock
            ->expects($this->once())
            ->method('getFieldsActivationJavaScriptCode')
            ->willReturn('foo');

        $javaScriptAssetHandlerConnector
            ->method('getFieldsActivationJavaScriptAssetHandler')
            ->willReturn($fieldsActivationJavaScriptAssetHandlerMock);

        /** @var FieldsValidationActivationJavaScriptAssetHandler|\PHPUnit_Framework_MockObject_MockObject $formInitializationJavaScriptAssetHandlerMock */
        $fieldsValidationActivationJavaScriptAssetHandlerMock = $this->getMock(
            FieldsValidationActivationJavaScriptAssetHandler::class,
            ['getFieldsValidationActivationJavaScriptCode'],
            [$assetHandlerFactory]
        );

        $fieldsValidationActivationJavaScriptAssetHandlerMock
            ->expects($this->once())
            ->method('getFieldsValidationActivationJavaScriptCode')
            ->willReturn('foo');

        $javaScriptAssetHandlerConnector
            ->method('getFieldsValidationActivationJavaScriptAssetHandler')
            ->willReturn($fieldsValidationActivationJavaScriptAssetHandlerMock);

        $javaScriptAssetHandlerConnector->generateAndIncludeJavaScript();
    }
}
