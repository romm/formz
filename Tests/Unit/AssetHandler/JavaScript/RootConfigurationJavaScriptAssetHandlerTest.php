<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\JavaScript\RootConfigurationJavaScriptAssetHandler;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\AssetHandler\AssetHandlerTestTrait;

class RootConfigurationJavaScriptAssetHandlerTest extends AbstractUnitTest
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

        /** @var RootConfigurationJavaScriptAssetHandler|\PHPUnit_Framework_MockObject_MockObject $assetHandler */
        $assetHandler = $this->getMockBuilder(RootConfigurationJavaScriptAssetHandler::class)
            ->setMethods(['handleRootConfiguration'])
            ->setConstructorArgs([$assetHandlerFactory])
            ->getMock();

        $jsonRootConfiguration = '';
        $assetHandler->method('handleRootConfiguration')
            ->willReturnCallback(
                function ($rootConfiguration) use (&$jsonRootConfiguration) {
                    $jsonRootConfiguration = $rootConfiguration;

                    return $rootConfiguration;
                }
            );

        $javaScriptCode = $assetHandler->getJavaScriptCode();

        $this->assertNotNull($jsonRootConfiguration);
        $this->assertEquals(
            str_replace('#CONFIGURATION#', $this->trimString($jsonRootConfiguration), $expectedResult),
            $this->removeMultiLinesComments($this->trimString($javaScriptCode))
        );

        $rootConfiguration = $assetHandlerFactory
            ->getFormObject()
            ->getDefinition()
            ->getRootConfiguration();
        $rootConfiguration->calculateHash();
        $hash = sha1($rootConfiguration->getHash());

        $this->assertNotFalse(strpos($assetHandler->getJavaScriptFileName(), $hash));

        unset($assetHandlerFactory);
    }
}
