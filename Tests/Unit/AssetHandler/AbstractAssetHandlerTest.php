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

        /** @var DefaultAssetHandler $defaultAssetHandler */
        $defaultAssetHandler = $assetHandlerFactory->getAssetHandler(DefaultAssetHandler::class);
        $defaultAssetHandler->callFunction(
                function () use (&$foo, $bar) {
                    $foo = $bar;
                }
            );

        $this->assertEquals($foo, $bar);
        $this->assertSame($formObject, $defaultAssetHandler->getFormObject());

        /*
         * Getting the same asset handler type with the same factory must return
         * the same instance.
         */
        /** @var DefaultAssetHandler $defaultAssetHandler2 */
        $defaultAssetHandler2 = $assetHandlerFactory->getAssetHandler(DefaultAssetHandler::class);
        $this->assertSame($defaultAssetHandler, $defaultAssetHandler2);

        /*
         * Getting the same asset handler type with another factory must return
         * a new instance.
         */
        $formObject2 = clone $formObject;
        $assetHandlerFactory2 = AssetHandlerFactory::get($formObject2, $controllerContext);
        $defaultAssetHandler3 = $assetHandlerFactory2->getAssetHandler(DefaultAssetHandler::class);
        $this->assertNotSame($defaultAssetHandler, $defaultAssetHandler3);
    }
}
