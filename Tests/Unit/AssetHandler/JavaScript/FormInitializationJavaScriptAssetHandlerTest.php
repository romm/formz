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
        $expectedResult = <<<TXT
(function(){Fz.Form.register('foo',#CONFIGURATION#);})();
TXT;

        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance(DefaultForm::class);

        /** @var FormInitializationJavaScriptAssetHandler|\PHPUnit_Framework_MockObject_MockObject $formInitializationJavaScriptAssetHandler */
        $formInitializationJavaScriptAssetHandler = $this->getMockBuilder(FormInitializationJavaScriptAssetHandler::class)
            ->setMethods(['handleFormConfiguration'])
            ->setConstructorArgs([$assetHandlerFactory])
            ->getMock();

        $jsonFormConfiguration = '';
        $formInitializationJavaScriptAssetHandler->method('handleFormConfiguration')
            ->willReturnCallback(
                function ($formConfiguration) use (&$jsonFormConfiguration) {
                    $jsonFormConfiguration = $formConfiguration;

                    return $formConfiguration;
                }
            );

        $javaScriptCode = $formInitializationJavaScriptAssetHandler->getFormInitializationJavaScriptCode();

        $this->assertNotNull($jsonFormConfiguration);
        $this->assertEquals(
            str_replace('#CONFIGURATION#', $this->trimString($jsonFormConfiguration), $expectedResult),
            $this->removeMultiLinesComments($this->trimString($javaScriptCode))
        );

        unset($assetHandlerFactory);
    }
}
