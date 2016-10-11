<?php
namespace Romm\Formz\Tests\Unit\AssetHandler;

use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\Tests\Fixture\AssetHandler\DefaultAssetHandler;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;

class AbstractAssetHandlerTest extends AbstractUnitTest
{

    /**
     * Checks that an asset handler is correctly instantiated and initialized.
     *
     * @test
     */
    public function assetHandlerIsInitializedCorrectly()
    {
        $formObject = $this->getFormObject();
        $controllerContext = new ControllerContext;
        $foo = 'foo';
        $bar = 'bar';

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);

        $defaultAssetHandler = DefaultAssetHandler::with($assetHandlerFactory)
            ->callFunction(
                function () use (&$foo, $bar) {
                    $foo = $bar;
                }
            );

        $this->assertEquals($foo, $bar);
        $this->assertSame($formObject, $defaultAssetHandler->getFormObject());
        $this->assertSame($formObject->getConfiguration(), $defaultAssetHandler->getFormConfiguration());

        /*
         * Getting the same asset handler type with the same factory must return
         * the same instance.
         */
        $defaultAssetHandler2 = DefaultAssetHandler::with($assetHandlerFactory);
        $this->assertSame($defaultAssetHandler, $defaultAssetHandler2);

        /*
         * Getting the same asset handler type with another factory must return
         * a new instance.
         */
        $assetHandlerFactory2 = clone $assetHandlerFactory;
        $defaultAssetHandler3 = DefaultAssetHandler::with($assetHandlerFactory2);
        $this->assertNotSame($defaultAssetHandler, $defaultAssetHandler3);
    }
}
