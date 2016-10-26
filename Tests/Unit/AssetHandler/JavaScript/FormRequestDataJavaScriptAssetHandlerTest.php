<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\JavaScript\FormRequestDataJavaScriptAssetHandler;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\AssetHandler\AssetHandlerTestTrait;
use TYPO3\CMS\Extbase\Error\Error;
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
        // MD5 of the JavaScript code result.
        $expectedResult = '51b1cc8b830784a363efb9bd1488599b';

        $originalRequest = new Request();
        $originalRequest->setArgument('foo', ['foo' => 'foo']);

        $request = new Request();
        $request->setOriginalRequest($originalRequest);

        $result = new Result();
        $result->forProperty('foo')
            ->forProperty('foo')
            ->addError(new Error('error', 42, [], 'foo:bar'));
        $request->setOriginalRequestMappingResults($result);

        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance(DefaultForm::class);
        $assetHandlerFactory->getControllerContext()->setRequest($request);

        /** @var FormRequestDataJavaScriptAssetHandler $formRequestDataJavaScriptAssetHandler */
        $formRequestDataJavaScriptAssetHandler = $assetHandlerFactory->getAssetHandler(FormRequestDataJavaScriptAssetHandler::class);
        $javaScriptCode = $formRequestDataJavaScriptAssetHandler->getFormRequestDataJavaScriptCode();

        $this->assertEquals(
            $expectedResult,
            md5($this->removeMultiLinesComments($this->trimString($javaScriptCode)))
        );

        unset($assetHandlerFactory);
    }
}
