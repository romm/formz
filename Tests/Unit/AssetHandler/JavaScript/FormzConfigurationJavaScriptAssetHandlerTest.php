<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\JavaScript\FormzConfigurationJavaScriptAssetHandler;
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
(function(){Fz.setConfiguration(#CONFIGURATION#);})();
TXT;

        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance();

        /** @var FormzConfigurationJavaScriptAssetHandler|\PHPUnit_Framework_MockObject_MockObject $assetHandler */
        $assetHandler = $this->getMockBuilder(FormzConfigurationJavaScriptAssetHandler::class)
            ->setMethods(['handleFormzConfiguration'])
            ->setConstructorArgs([$assetHandlerFactory])
            ->getMock();

        $jsonFormzConfiguration = '';
        $assetHandler->method('handleFormzConfiguration')
            ->willReturnCallback(
                function ($formzConfiguration) use (&$jsonFormzConfiguration) {
                    $jsonFormzConfiguration = $formzConfiguration;

                    return $formzConfiguration;
                }
            );

        $javaScriptCode = $assetHandler->getJavaScriptCode();

        $this->assertNotNull($jsonFormzConfiguration);
        $this->assertEquals(
            str_replace('#CONFIGURATION#', $this->trimString($jsonFormzConfiguration), $expectedResult),
            $this->removeMultiLinesComments($this->trimString($javaScriptCode))
        );

        $rootConfiguration = $assetHandlerFactory
            ->getFormObject()
            ->getConfiguration()
            ->getRootConfiguration();
        $rootConfiguration->calculateHash();
        $hash = sha1($rootConfiguration->getHash());

        $this->assertNotFalse(strpos($assetHandler->getJavaScriptFileName(), $hash));

        unset($assetHandlerFactory);
    }
}
