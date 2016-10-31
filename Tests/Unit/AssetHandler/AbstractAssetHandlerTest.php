<?php
namespace Romm\Formz\Tests\Unit\AssetHandler;

use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\Tests\Fixture\AssetHandler\DefaultAssetHandler;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;

class AbstractAssetHandlerTest extends AbstractUnitTest
{

    /**
     * Checks that an asset handler is built correctly.
     *
     * @test
     */
    public function assetHandlerIsBuiltCorrectly()
    {
        $formObject = $this->getFormObject();
        $controllerContext = new ControllerContext;

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);

        $assetHandler = new DefaultAssetHandler($assetHandlerFactory);

        $this->assertSame($assetHandlerFactory->getFormObject(), $assetHandler->getFormObject());
        $this->assertSame($assetHandlerFactory->getControllerContext(), $assetHandler->getControllerContext());
    }
}
