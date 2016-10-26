<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\JavaScript\FormInitializationJavaScriptAssetHandler;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\AssetHandler\AssetHandlerTestTrait;

class FormInitializationJavaScriptAssetHandlerTest extends AbstractUnitTest
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
        $expectedResult = '890a5bc88b7c6e37a641aef1825839b7';

        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance(DefaultForm::class);

        /** @var FormInitializationJavaScriptAssetHandler $formInitializationJavaScriptAssetHandler */
        $formInitializationJavaScriptAssetHandler = $assetHandlerFactory->getAssetHandler(FormInitializationJavaScriptAssetHandler::class);
        $javaScriptCode = $formInitializationJavaScriptAssetHandler->getFormInitializationJavaScriptCode();

        $this->assertEquals(
            $expectedResult,
            md5($this->removeMultiLinesComments($this->trimString($javaScriptCode)))
        );

        unset($assetHandlerFactory);
    }
}
