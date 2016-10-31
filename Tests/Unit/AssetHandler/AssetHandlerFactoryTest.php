<?php
namespace Romm\Formz\Tests\Unit\AssetHandler;

use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\Exceptions\ClassNotFoundException;
use Romm\Formz\Exceptions\InvalidArgumentTypeException;
use Romm\Formz\Tests\Fixture\AssetHandler\DefaultAssetHandler;
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

        $this->assertInstanceOf(AssetHandlerFactory::class, $assetHandlerFactory);
        $this->assertSame($formObject, $assetHandlerFactory->getFormObject());
        $this->assertSame($controllerContext, $assetHandlerFactory->getControllerContext());

        $assetHandlerFactory2 = AssetHandlerFactory::get($formObject, $controllerContext);

        $this->assertSame($assetHandlerFactory, $assetHandlerFactory2);

        $formObject2 = clone $formObject;
        $assetHandlerFactory3 = AssetHandlerFactory::get($formObject2, $controllerContext);

        $this->assertNotSame($assetHandlerFactory, $assetHandlerFactory3);

        unset($formObject);
        unset($controllerContext);
        unset($assetHandlerFactory);
        unset($assetHandlerFactory2);
        unset($assetHandlerFactory3);
    }

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

        unset($formObject);
        unset($formObject2);
        unset($controllerContext);
        unset($defaultAssetHandler);
        unset($defaultAssetHandler2);
        unset($defaultAssetHandler3);
        unset($assetHandlerFactory);
        unset($assetHandlerFactory2);
        unset($assetHandlerFactory23);
    }

    /**
     * Trying to get an asset handler with a non-existing class name must throw
     * an exception.
     *
     * @test
     */
    public function gettingAssetHandlerWithWrongClassNameThrowsException()
    {
        $this->setExpectedException(ClassNotFoundException::class);

        $formObject = $this->getFormObject();
        $controllerContext = new ControllerContext;

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);

        $assetHandlerFactory->getAssetHandler('SomeWrongClassName1337');
    }

    /**
     * Trying to get an asset handler with a not-valid class name must throw an
     * exception.
     *
     * @test
     */
    public function gettingWrongAssetHandlerThrowsException()
    {
        $this->setExpectedException(InvalidArgumentTypeException::class);

        $formObject = $this->getFormObject();
        $controllerContext = new ControllerContext;

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);

        $assetHandlerFactory->getAssetHandler(\stdClass::class);
    }
}
