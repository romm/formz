<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\JavaScript\FormRequestDataJavaScriptAssetHandler;
use Romm\Formz\Error\Error;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\AssetHandler\AssetHandlerTestTrait;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Mvc\Request;

class FormRequestDataJavaScriptAssetHandlerTest extends AbstractUnitTest
{
    use AssetHandlerTestTrait;

    /**
     * Checks that the generated JavaScript code is valid.
     *
     * @test
     */
    public function checkJavaScriptCode()
    {
        $expectedResult = <<<TXT
(function(){Formz.Form.beforeInitialization('foo',function(form){form.injectRequestData({"foo":"foo"},{"foo":{"foo":{"bar":"error"}}},true)});})();
TXT;

        $originalRequest = new Request();
        $originalRequest->setArgument('foo', ['foo' => 'foo']);

        $request = new Request();
        $request->setOriginalRequest($originalRequest);

        $result = new Result();
        $error = new Error(
            'error',
            42,
            [],
            '',
            'foo',
            'bar'
        );

        $result->forProperty('foo')
            ->forProperty('foo')
            ->addError($error);
        $request->setOriginalRequestMappingResults($result);

        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance(DefaultForm::class);
        $assetHandlerFactory->getControllerContext()->setRequest($request);

        /** @var FormRequestDataJavaScriptAssetHandler $formRequestDataJavaScriptAssetHandler */
        $formRequestDataJavaScriptAssetHandler = $assetHandlerFactory->getAssetHandler(FormRequestDataJavaScriptAssetHandler::class);
        $javaScriptCode = $formRequestDataJavaScriptAssetHandler->getFormRequestDataJavaScriptCode();

        $this->assertEquals(
            $expectedResult,
            $this->removeMultiLinesComments($this->trimString($javaScriptCode))
        );

        unset($assetHandlerFactory);
    }
}
