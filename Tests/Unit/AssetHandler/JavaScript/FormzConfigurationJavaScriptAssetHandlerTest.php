<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\JavaScript\FormzConfigurationJavaScriptAssetHandler;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\AssetHandler\AssetHandlerTestTrait;

class FormzConfigurationJavaScriptAssetHandlerTest extends AbstractUnitTest
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
(function(){Formz.setConfiguration(#CONFIGURATION#);})();
TXT;

        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance(DefaultForm::class);

        /** @var FormzConfigurationJavaScriptAssetHandler|\PHPUnit_Framework_MockObject_MockObject $formzConfigurationJavaScriptAssetHandler */
        $formzConfigurationJavaScriptAssetHandler = $this->getMock(FormzConfigurationJavaScriptAssetHandler::class, ['handleFormzConfiguration'], [$assetHandlerFactory]);

        $jsonFormzConfiguration = '';
        $formzConfigurationJavaScriptAssetHandler->method('handleFormzConfiguration')
            ->willReturnCallback(
                function ($formzConfiguration) use (&$jsonFormzConfiguration) {
                    $jsonFormzConfiguration = $formzConfiguration;

                    return $formzConfiguration;
                }
            );

        $javaScriptCode = $formzConfigurationJavaScriptAssetHandler->getJavaScriptCode();

        $this->assertNotNull($jsonFormzConfiguration);
        $this->assertEquals(
            str_replace('#CONFIGURATION#', $this->trimString($jsonFormzConfiguration), $expectedResult),
            $this->removeMultiLinesComments($this->trimString($javaScriptCode))
        );

        $hash = $assetHandlerFactory
            ->getFormObject()
            ->getConfiguration()
            ->getFormzConfiguration()
            ->getHash();

        $this->assertNotFalse(strpos($formzConfigurationJavaScriptAssetHandler->getJavaScriptFileName(), $hash));

        unset($assetHandlerFactory);
    }
}
