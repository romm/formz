<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\Connector;

use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\AssetHandler\Connector\AssetHandlerConnectorManager;
use Romm\Formz\AssetHandler\Connector\AssetHandlerConnectorStates;
use Romm\Formz\AssetHandler\Connector\JavaScriptAssetHandlerConnector;
use Romm\Formz\AssetHandler\JavaScript\FieldsActivationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FieldsValidationActivationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FieldsValidationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FormInitializationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FormRequestDataJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\LocalizationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\RootConfigurationJavaScriptAssetHandler;
use Romm\Formz\Condition\Processor\ConditionProcessor;
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

        $formObject = $this->getDefaultFormObject();
        $controllerContext = new ControllerContext;

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);

        /** @var PageRenderer|\PHPUnit_Framework_MockObject_MockObject $pageRendererMock */
        $pageRendererMock = $this->getMockBuilder(PageRenderer::class)
            ->setMethods(['addJsFooterFile'])
            ->getMock();
        $pageRendererMock->expects($this->atLeastOnce())
            ->method('addJsFooterFile')
            ->willReturnCallback(function () use (&$filesIncluded) {
                $filesIncluded++;
            });

        $assetHandlerConnectorManager = new AssetHandlerConnectorManager($pageRendererMock, $assetHandlerFactory);
        $javaScriptAssetHandlerConnector = new JavaScriptAssetHandlerConnector($assetHandlerConnectorManager);
        $javaScriptAssetHandlerConnector->injectEnvironmentService($this->getMockedEnvironmentService());
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
        $pageRendererMockBis = $this->getMockBuilder(PageRenderer::class)
            ->setMethods(['addJsFooterFile'])
            ->getMock();
        $pageRendererMockBis->expects($this->atLeastOnce())
            ->method('addJsFooterFile')
            ->willReturnCallback(function () use (&$filesIncludedBis) {
                $filesIncludedBis++;
            });

        $assetHandlerConnectorManagerBis = new AssetHandlerConnectorManager($pageRendererMockBis, $assetHandlerFactory);
        $javaScriptAssetHandlerConnectorBis = new JavaScriptAssetHandlerConnector($assetHandlerConnectorManagerBis);
        $javaScriptAssetHandlerConnectorBis->injectEnvironmentService($this->getMockedEnvironmentService());
        $javaScriptAssetHandlerConnectorBis->includeDefaultJavaScriptFiles();

        $this->assertGreaterThan($filesIncluded, $filesIncludedBis);
    }

    /**
     * Checks that the FormZ JavaScript configuration is correctly included with
     * the page renderer.
     *
     * @test
     */
    public function formzConfigurationIsGeneratedAndIncluded()
    {
        $formObject = $this->getDefaultFormObject();
        $controllerContext = new ControllerContext;

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);

        /** @var PageRenderer|\PHPUnit_Framework_MockObject_MockObject $pageRendererMock */
        $pageRendererMock = $this->getMockBuilder(PageRenderer::class)
            ->setMethods(['addJsFooterFile'])
            ->getMock();
        $pageRendererMock->expects($this->once())
            ->method('addJsFooterFile');

        /** @var AssetHandlerConnectorManager|\PHPUnit_Framework_MockObject_MockObject $assetHandlerConnectorManagerMock */
        $assetHandlerConnectorManagerMock = $this->getMockBuilder(AssetHandlerConnectorManager::class)
            ->setMethods(['writeTemporaryFile'])
            ->setConstructorArgs([$pageRendererMock, $assetHandlerFactory])
            ->getMock();

        /** @var JavaScriptAssetHandlerConnector|\PHPUnit_Framework_MockObject_MockObject $javaScriptAssetHandlerConnector */
        $javaScriptAssetHandlerConnector = $this->getMockBuilder(JavaScriptAssetHandlerConnector::class)
            ->setMethods(['getFormzConfigurationJavaScriptAssetHandler'])
            ->setConstructorArgs([$assetHandlerConnectorManagerMock])
            ->getMock();

        $javaScriptAssetHandlerConnector->injectEnvironmentService($this->getMockedEnvironmentService());

        /** @var RootConfigurationJavaScriptAssetHandler|\PHPUnit_Framework_MockObject_MockObject $formzConfigurationJavaScriptAssetHandlerMock */
        $formzConfigurationJavaScriptAssetHandlerMock = $this->getMockBuilder(RootConfigurationJavaScriptAssetHandler::class)
            ->setMethods(['getJavaScriptFileName', 'getJavaScriptCode'])
            ->setConstructorArgs([$assetHandlerFactory])
            ->getMock();

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
        $formObject = $this->getDefaultFormObject();
        $controllerContext = new ControllerContext;

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);

        /** @var PageRenderer|\PHPUnit_Framework_MockObject_MockObject $pageRendererMock */
        $pageRendererMock = $this->getMockBuilder(PageRenderer::class)
            ->setMethods(['addJsFooterFile'])
            ->getMock();
        $pageRendererMock->expects($this->once())
            ->method('addJsFooterFile');

        /** @var AssetHandlerConnectorManager|\PHPUnit_Framework_MockObject_MockObject $assetHandlerConnectorManagerMock */
        $assetHandlerConnectorManagerMock = $this->getMockBuilder(AssetHandlerConnectorManager::class)
            ->setMethods(['fileExists', 'writeTemporaryFile'])
            ->setConstructorArgs([$pageRendererMock, $assetHandlerFactory])
            ->getMock();

        $assetHandlerConnectorManagerMock
            ->method('fileExists')
            ->willReturn(false);

        /** @var JavaScriptAssetHandlerConnector|\PHPUnit_Framework_MockObject_MockObject $javaScriptAssetHandlerConnector */
        $javaScriptAssetHandlerConnector = $this->getMockBuilder(JavaScriptAssetHandlerConnector::class)
            ->setMethods([
                'getFormInitializationJavaScriptAssetHandler',
                'getFieldsValidationJavaScriptAssetHandler',
                'getFieldsActivationJavaScriptAssetHandler',
                'getFieldsValidationActivationJavaScriptAssetHandler'
            ])
            ->setConstructorArgs([$assetHandlerConnectorManagerMock])
            ->getMock();

        $javaScriptAssetHandlerConnector->injectEnvironmentService($this->getMockedEnvironmentService());

        $formInitializationJavaScriptAssetHandlerMock = $this->getMockBuilder(FormInitializationJavaScriptAssetHandler::class)
            ->setMethods(['getFormInitializationJavaScriptCode'])
            ->setConstructorArgs([$assetHandlerFactory])
            ->getMock();

        $formInitializationJavaScriptAssetHandlerMock
            ->expects($this->once())
            ->method('getFormInitializationJavaScriptCode')
            ->willReturn('foo');

        $javaScriptAssetHandlerConnector
            ->method('getFormInitializationJavaScriptAssetHandler')
            ->willReturn($formInitializationJavaScriptAssetHandlerMock);

        $fieldsValidationJavaScriptAssetHandlerMock = $this->getMockBuilder(FieldsValidationJavaScriptAssetHandler::class)
            ->setMethods(['getJavaScriptCode'])
            ->setConstructorArgs([$assetHandlerFactory])
            ->getMock();

        $fieldsValidationJavaScriptAssetHandlerMock
            ->expects($this->once())
            ->method('getJavaScriptCode')
            ->willReturn('foo');

        $javaScriptAssetHandlerConnector
            ->method('getFieldsValidationJavaScriptAssetHandler')
            ->willReturn($fieldsValidationJavaScriptAssetHandlerMock);

        $fieldsActivationJavaScriptAssetHandlerMock = $this->getMockBuilder(FieldsActivationJavaScriptAssetHandler::class)
            ->setMethods(['getFieldsActivationJavaScriptCode'])
            ->setConstructorArgs([$assetHandlerFactory])
            ->getMock();

        $fieldsActivationJavaScriptAssetHandlerMock
            ->expects($this->once())
            ->method('getFieldsActivationJavaScriptCode')
            ->willReturn('foo');

        $javaScriptAssetHandlerConnector
            ->method('getFieldsActivationJavaScriptAssetHandler')
            ->willReturn($fieldsActivationJavaScriptAssetHandlerMock);

        /** @var FieldsValidationActivationJavaScriptAssetHandler|\PHPUnit_Framework_MockObject_MockObject $formInitializationJavaScriptAssetHandlerMock */
        $fieldsValidationActivationJavaScriptAssetHandlerMock = $this->getMockBuilder(FieldsValidationActivationJavaScriptAssetHandler::class)
            ->setMethods(['getFieldsValidationActivationJavaScriptCode'])
            ->setConstructorArgs([$assetHandlerFactory])
            ->getMock();

        $fieldsValidationActivationJavaScriptAssetHandlerMock
            ->expects($this->once())
            ->method('getFieldsValidationActivationJavaScriptCode')
            ->willReturn('foo');

        $javaScriptAssetHandlerConnector
            ->method('getFieldsValidationActivationJavaScriptAssetHandler')
            ->willReturn($fieldsValidationActivationJavaScriptAssetHandlerMock);

        $javaScriptAssetHandlerConnector->generateAndIncludeJavaScript();
    }

    /**
     * Checks that inline JavaScript code is generated and added to the page.
     *
     * @param bool                                             $activateDebugMode
     * @param \PHPUnit_Framework_MockObject_Matcher_Invocation $debugExpect
     * @dataProvider inlineJavaScriptIsGeneratedAndIncludedDataProvider
     * @test
     */
    public function inlineJavaScriptIsGeneratedAndIncluded($activateDebugMode, \PHPUnit_Framework_MockObject_Matcher_Invocation $debugExpect)
    {
        $formObject = $this->getDefaultFormObject();
        $controllerContext = new ControllerContext;

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);

        /** @var PageRenderer|\PHPUnit_Framework_MockObject_MockObject $pageRendererMock */
        $pageRendererMock = $this->getMockBuilder(PageRenderer::class)
            ->setMethods(['addJsFooterInlineCode'])
            ->getMock();
        $pageRendererMock->expects($this->once())
            ->method('addJsFooterInlineCode');

        $assetHandlerConnectorManager = new AssetHandlerConnectorManager($pageRendererMock, $assetHandlerFactory);

        /** @var JavaScriptAssetHandlerConnector|\PHPUnit_Framework_MockObject_MockObject $javaScriptAssetHandlerConnector */
        $javaScriptAssetHandlerConnector = $this->getMockBuilder(JavaScriptAssetHandlerConnector::class)
            ->setMethods([
                'getFormRequestDataJavaScriptAssetHandler',
                'getAjaxUrl',
                'getDebugActivationCode'
            ])
            ->setConstructorArgs([$assetHandlerConnectorManager])
            ->getMock();

        $javaScriptAssetHandlerConnector->injectEnvironmentService($this->getMockedEnvironmentService());

        $formRequestDataJavaScriptAssetHandlerMock = $this->getMockBuilder(FormRequestDataJavaScriptAssetHandler::class)
            ->setMethods(['getFormRequestDataJavaScriptCode'])
            ->setConstructorArgs([$assetHandlerFactory])
            ->getMock();

        $formRequestDataJavaScriptAssetHandlerMock
            ->expects($this->once())
            ->method('getFormRequestDataJavaScriptCode')
            ->willReturn('foo');

        $javaScriptAssetHandlerConnector
            ->method('getFormRequestDataJavaScriptAssetHandler')
            ->willReturn($formRequestDataJavaScriptAssetHandlerMock);

        $javaScriptAssetHandlerConnector
            ->method('getAjaxUrl')
            ->willReturn('foo');

        $this->setExtensionConfigurationValue('debugMode', $activateDebugMode);

        $javaScriptAssetHandlerConnector
            ->expects($debugExpect)
            ->method('getDebugActivationCode')
            ->willReturn('foo');

        $javaScriptAssetHandlerConnector->generateAndIncludeInlineJavaScript();
    }

    /**
     * Data provider for function `inlineJavaScriptIsGeneratedAndIncluded`.
     *
     * @return array
     */
    public function inlineJavaScriptIsGeneratedAndIncludedDataProvider()
    {
        return [
            [false, $this->never()],
            [true, $this->once()]
        ];
    }

    /**
     * Checks that the language JavaScript files are generated and included.
     *
     * @test
     */
    public function languageJavaScriptFilesAreIncluded()
    {
        $formObject = $this->getDefaultFormObject();
        $controllerContext = new ControllerContext;

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);

        /** @var PageRenderer|\PHPUnit_Framework_MockObject_MockObject $pageRendererMock */
        $pageRendererMock = $this->getMockBuilder(PageRenderer::class)
            ->setMethods(['addJsFooterFile'])
            ->getMock();
        $pageRendererMock->expects($this->once())
            ->method('addJsFooterFile');

        /** @var AssetHandlerConnectorManager|\PHPUnit_Framework_MockObject_MockObject $assetHandlerConnectorManagerMock */
        $assetHandlerConnectorManagerMock = $this->getMockBuilder(AssetHandlerConnectorManager::class)
            ->setMethods(['fileExists', 'writeTemporaryFile'])
            ->setConstructorArgs([$pageRendererMock, $assetHandlerFactory])
            ->getMock();

        $assetHandlerConnectorManagerMock
            ->method('fileExists')
            ->willReturn(false);

        /** @var JavaScriptAssetHandlerConnector|\PHPUnit_Framework_MockObject_MockObject $javaScriptAssetHandlerConnector */
        $javaScriptAssetHandlerConnector = $this->getMockBuilder(JavaScriptAssetHandlerConnector::class)
            ->setMethods(['getFormzLocalizationJavaScriptAssetHandler'])
            ->setConstructorArgs([$assetHandlerConnectorManagerMock])
            ->getMock();

        $javaScriptAssetHandlerConnector->injectEnvironmentService($this->getMockedEnvironmentService());

        $formzLocalizationJavaScriptAssetHandlerMock = $this->getMockBuilder(LocalizationJavaScriptAssetHandler::class)
            ->setMethods(['injectTranslationsForFormFieldsValidator', 'getJavaScriptCode'])
            ->setConstructorArgs([$assetHandlerFactory])
            ->getMock();

        $formzLocalizationJavaScriptAssetHandlerMock
            ->expects($this->once())
            ->method('injectTranslationsForFormFieldsValidator')
            ->willReturn($formzLocalizationJavaScriptAssetHandlerMock);

        $formzLocalizationJavaScriptAssetHandlerMock
            ->expects($this->once())
            ->method('getJavaScriptCode');

        $javaScriptAssetHandlerConnector
            ->method('getFormzLocalizationJavaScriptAssetHandler')
            ->willReturn($formzLocalizationJavaScriptAssetHandlerMock);

        $javaScriptAssetHandlerConnector->includeLanguageJavaScriptFiles();
    }

    /**
     * Checks that the JavaScript files required by validation rules and
     * conditions are correctly included, and only once by file.
     *
     * @test
     */
    public function javaScriptValidationAndConditionFilesAreIncludedOnce()
    {
        $formObject = $this->getDefaultFormObject();
        $controllerContext = new ControllerContext;

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);

        /** @var PageRenderer|\PHPUnit_Framework_MockObject_MockObject $pageRendererMock */
        $pageRendererMock = $this->getMockBuilder(PageRenderer::class)
            ->setMethods(['addJsFooterFile'])
            ->getMock();
        $pageRendererMock->expects($this->exactly(3))
            ->method('addJsFooterFile');

        $assetHandlerConnectorManager = new AssetHandlerConnectorManager($pageRendererMock, $assetHandlerFactory);

        $assetHandlerConnectorStates = new AssetHandlerConnectorStates;
        $assetHandlerConnectorManager->injectAssetHandlerConnectorStates($assetHandlerConnectorStates);

        /*
         * We will test on a first connector: one validation file and one
         * condition file will be included.
         */
        /** @var JavaScriptAssetHandlerConnector|\PHPUnit_Framework_MockObject_MockObject $javaScriptAssetHandlerConnector */
        $javaScriptAssetHandlerConnector = $this->getMockBuilder(JavaScriptAssetHandlerConnector::class)
            ->setMethods(['getConditionProcessor', 'getFieldsValidationJavaScriptAssetHandler'])
            ->setConstructorArgs([$assetHandlerConnectorManager])
            ->getMock();

        $javaScriptAssetHandlerConnector->injectEnvironmentService($this->getMockedEnvironmentService());

        $conditionProcessorMock = $this->getMockBuilder(ConditionProcessor::class)
            ->setMethods(['getJavaScriptFiles'])
            ->setConstructorArgs([$formObject])
            ->getMock();

        $conditionProcessorMock
            ->expects($this->once())
            ->method('getJavaScriptFiles')
            ->willReturn(['foo']);

        $javaScriptAssetHandlerConnector
            ->method('getConditionProcessor')
            ->willReturn($conditionProcessorMock);

        $fieldsValidationJavaScriptAssetHandlerMock = $this->getMockBuilder(FieldsValidationJavaScriptAssetHandler::class)
            ->setMethods(['getJavaScriptValidationFiles'])
            ->setConstructorArgs([$assetHandlerFactory])
            ->getMock();

        $fieldsValidationJavaScriptAssetHandlerMock
            ->expects($this->once())
            ->method('getJavaScriptValidationFiles')
            ->willReturn(['bar']);

        $javaScriptAssetHandlerConnector
            ->method('getFieldsValidationJavaScriptAssetHandler')
            ->willReturn($fieldsValidationJavaScriptAssetHandlerMock);

        $javaScriptAssetHandlerConnector->includeJavaScriptValidationAndConditionFiles();

        /*
         * Second part: a second connector will include more JavaScript files,
         * but on three files, only one is new since the last connector.
         */
        /** @var JavaScriptAssetHandlerConnector|\PHPUnit_Framework_MockObject_MockObject $javaScriptAssetHandlerConnector */
        $javaScriptAssetHandlerConnector = $this->getMockBuilder(JavaScriptAssetHandlerConnector::class)
            ->setMethods(['getConditionProcessor', 'getFieldsValidationJavaScriptAssetHandler'])
            ->setConstructorArgs([$assetHandlerConnectorManager])
            ->getMock();

        $javaScriptAssetHandlerConnector->injectEnvironmentService($this->getMockedEnvironmentService());

        $conditionProcessorMock = $this->getMockBuilder(ConditionProcessor::class)
            ->setMethods(['getJavaScriptFiles'])
            ->setConstructorArgs([$formObject])
            ->getMock();

        $conditionProcessorMock
            ->expects($this->once())
            ->method('getJavaScriptFiles')
            ->willReturn(['foo', 'hello']);

        $javaScriptAssetHandlerConnector
            ->method('getConditionProcessor')
            ->willReturn($conditionProcessorMock);

        $fieldsValidationJavaScriptAssetHandlerMock = $this->getMockBuilder(FieldsValidationJavaScriptAssetHandler::class)
            ->setMethods(['getJavaScriptValidationFiles'])
            ->setConstructorArgs([$assetHandlerFactory])
            ->getMock();

        $fieldsValidationJavaScriptAssetHandlerMock
            ->expects($this->once())
            ->method('getJavaScriptValidationFiles')
            ->willReturn(['bar']);

        $javaScriptAssetHandlerConnector
            ->method('getFieldsValidationJavaScriptAssetHandler')
            ->willReturn($fieldsValidationJavaScriptAssetHandlerMock);

        $javaScriptAssetHandlerConnector->includeJavaScriptValidationAndConditionFiles();
    }
}
