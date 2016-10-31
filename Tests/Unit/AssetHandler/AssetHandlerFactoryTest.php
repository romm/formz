<?php
namespace Romm\Formz\Tests\Unit\AssetHandler;

use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;

class AssetHandlerFactoryTest extends AbstractUnitTest
{

    /**
     * Checks that the asset handler factory is correctly initialized, and that
     * its getters work properly.
     *
     * Will also check that getting two time a factory for a given object will
     * return the same instance.
     *
     * @test
     */
    public function assetHandlerFactoryIsInitializedCorrectly()
    {
        $formObject = $this->getFormObject();
        $controllerContext = new ControllerContext;

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);

        $this->assertSame($formObject, $assetHandlerFactory->getFormObject());
        $this->assertSame($controllerContext, $assetHandlerFactory->getControllerContext());

        $assetHandlerFactory2 = AssetHandlerFactory::get($formObject, $controllerContext);

        $this->assertSame($assetHandlerFactory, $assetHandlerFactory2);

        $formObject2 = clone $formObject;
        $assetHandlerFactory3 = AssetHandlerFactory::get($formObject2, $controllerContext);

        $this->assertNotSame($assetHandlerFactory, $assetHandlerFactory3);
    }
}
