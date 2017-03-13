<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\Css;

use Romm\Formz\AssetHandler\Css\FeedbackContainerDisplayCssAssetHandler;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\AssetHandler\AssetHandlerTestTrait;

class FeedbackContainerDisplayCssAssetHandlerTest extends AbstractUnitTest
{
    use AssetHandlerTestTrait;

    /**
     * Checks that the CSS code returned by the asset handler is valid.
     *
     * @test
     */
    public function errorContainerDisplayCssIsValid()
    {
        $expectedCss = 'form[name="foo"]:not([fz-error-foo="1"]):not([fz-warning-foo="1"]):not([fz-notice-foo="1"])[fz-field-feedback-container="foo"]{display:none!important;}';

        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance();

        $assetHandler = new FeedbackContainerDisplayCssAssetHandler($assetHandlerFactory);
        $errorContainerDisplayCss = $assetHandler->getErrorContainerDisplayCss();

        $this->assertEquals($expectedCss, $this->trimString($errorContainerDisplayCss));

        unset($assetHandlerFactory);
    }
}
