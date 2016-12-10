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
use Romm\Formz\AssetHandler\JavaScript\FormzConfigurationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FormzLocalizationJavaScriptAssetHandler;
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
        $formObject = $this->getFormObject();
        $controllerContext = new ControllerContext;

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);

        /** @var PageRenderer|\PHPUnit_Framework_MockObject_MockObject $pageRendererMock */
        $pageRendererMock = $this->getMock(PageRenderer::class, ['addJsFooterInlineCode']);
        $pageRendererMock->expects($this->once())
            ->method('addJsFooterInlineCode');

        $assetHandlerConnectorManager = new AssetHandlerConnectorManager($pageRendererMock, $assetHandlerFactory);

        /** @var JavaScriptAssetHandlerConnector|\PHPUnit_Framework_MockObject_MockObject $javaScriptAssetHandlerConnector */
        $javaScriptAssetHandlerConnector = $this->getMock(
            JavaScriptAssetHandlerConnector::class,
            [
                'getFormRequestDataJavaScriptAssetHandler',
                'getAjaxUrl',
                'getDebugActivationCode'
            ],
            [$assetHandlerConnectorManager]
        );

        $formRequestDataJavaScriptAssetHandlerMock = $this->getMock(
            FormRequestDataJavaScriptAssetHandler::class,
            ['getFormRequestDataJavaScriptCode'],
            [$assetHandlerFactory]
        );

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
            ['getFormzLocalizationJavaScriptAssetHandler'],
            [$assetHandlerConnectorManagerMock]
        );

        $formzLocalizationJavaScriptAssetHandlerMock = $this->getMock(
            FormzLocalizationJavaScriptAssetHandler::class,
            ['injectTranslationsForFormFieldsValidation', 'getJavaScriptCode'],
            [$assetHandlerFactory]
        );

        $formzLocalizationJavaScriptAssetHandlerMock
            ->expects($this->once())
            ->method('injectTranslationsForFormFieldsValidation')
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
        $formObject = $this->getFormObject();
        $controllerContext = new ControllerContext;

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);

        /** @var PageRenderer|\PHPUnit_Framework_MockObject_MockObject $pageRendererMock */
        $pageRendererMock = $this->getMock(PageRenderer::class, ['addJsFooterFile']);
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
        $javaScriptAssetHandlerConnector = $this->getMock(
            JavaScriptAssetHandlerConnector::class,
            ['getConditionProcessor', 'getFieldsValidationJavaScriptAssetHandler'],
            [$assetHandlerConnectorManager]
        );

        $conditionProcessorMock = $this->getMock(
            ConditionProcessor::class,
            ['getJavaScriptFiles'],
            [$formObject]
        );

        $conditionProcessorMock
            ->expects($this->once())
            ->method('getJavaScriptFiles')
            ->willReturn(['foo']);

        $javaScriptAssetHandlerConnector
            ->method('getConditionProcessor')
            ->willReturn($conditionProcessorMock);


        $fieldsValidationJavaScriptAssetHandlerMock = $this->getMock(
            FieldsValidationJavaScriptAssetHandler::class,
            ['getJavaScriptValidationFiles'],
            [$assetHandlerFactory]
        );

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
        $javaScriptAssetHandlerConnector = $this->getMock(
            JavaScriptAssetHandlerConnector::class,
            ['getConditionProcessor', 'getFieldsValidationJavaScriptAssetHandler'],
            [$assetHandlerConnectorManager]
        );

        $conditionProcessorMock = $this->getMock(
            ConditionProcessor::class,
            ['getJavaScriptFiles'],
            [$formObject]
        );

        $conditionProcessorMock
            ->expects($this->once())
            ->method('getJavaScriptFiles')
            ->willReturn(['foo', 'hello']);

        $javaScriptAssetHandlerConnector
            ->method('getConditionProcessor')
            ->willReturn($conditionProcessorMock);


        $fieldsValidationJavaScriptAssetHandlerMock = $this->getMock(
            FieldsValidationJavaScriptAssetHandler::class,
            ['getJavaScriptValidationFiles'],
            [$assetHandlerFactory]
        );

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
