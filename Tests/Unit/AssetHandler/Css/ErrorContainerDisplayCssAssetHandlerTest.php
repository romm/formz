<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\Css;

use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\AssetHandler\Css\ErrorContainerDisplayCssAssetHandler;
use Romm\Formz\Core\Core;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\AssetHandler\AssetHandlerTestTrait;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;

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

        $form = Core::get()->getFormObjectFactory()->getInstanceFromClassName(DefaultForm::class, 'foo');
        $controllerContext = new ControllerContext();
        $assetHandlerFactory = AssetHandlerFactory::get($form, $controllerContext);

        $errorContainerDisplayCss = ErrorContainerDisplayCssAssetHandler::with($assetHandlerFactory)
            ->getErrorContainerDisplayCss();

        $this->assertEquals($this->trimString($errorContainerDisplayCss), $expectedCss);
    }
}
