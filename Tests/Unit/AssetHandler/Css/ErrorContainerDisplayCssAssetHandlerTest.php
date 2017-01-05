<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\Css;

use Romm\Formz\AssetHandler\Css\ErrorContainerDisplayCssAssetHandler;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\AssetHandler\AssetHandlerTestTrait;

class ErrorContainerDisplayCssAssetHandlerTest extends AbstractUnitTest
{
    use AssetHandlerTestTrait;

    /**
     * Checks that the CSS code returned by the asset handler is valid.
     *
     * @test
     */
    public function errorContainerDisplayCssIsValid()
    {
        $expectedCss = 'form[name="foo"]:not([formz-error-foo="1"])[formz-field-feedback-container="foo"]{display:none!important;}';

        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance(DefaultForm::class);

        /** @var ErrorContainerDisplayCssAssetHandler $errorContainerDisplayCssAssetHandler */
        $errorContainerDisplayCssAssetHandler = $assetHandlerFactory->getAssetHandler(ErrorContainerDisplayCssAssetHandler::class);
        $errorContainerDisplayCss = $errorContainerDisplayCssAssetHandler->getErrorContainerDisplayCss();

        $this->assertEquals($this->trimString($errorContainerDisplayCss), $expectedCss);

        unset($assetHandlerFactory);
    }
}
