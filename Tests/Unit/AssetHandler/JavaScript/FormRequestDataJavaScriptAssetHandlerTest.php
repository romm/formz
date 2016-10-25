<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\AssetHandler\JavaScript\FormRequestDataJavaScriptAssetHandler;
use Romm\Formz\Core\Core;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\AssetHandler\AssetHandlerTestTrait;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Request;

class FormRequestDataJavaScriptAssetHandlerTest extends AbstractUnitTest
{

    use AssetHandlerTestTrait;

    /**
     * Checks that the generated JavaScript code is valid.
     *
     * @test
     */
    public function checkJavaScriptCode()
    {
        // MD5 of the JavaScript code result.
        $expectedResult = '51b1cc8b830784a363efb9bd1488599b';

        $formObject = Core::get()->getFormObjectFactory()->getInstanceFromClassName(DefaultForm::class, 'foo');

        $originalRequest = new Request();
        $originalRequest->setArgument('foo', ['foo' => 'foo']);

        $request = new Request();
        $request->setOriginalRequest($originalRequest);

        $result = new Result();
        $result->forProperty('foo')
            ->forProperty('foo')
            ->addError(new Error('error', 42, [], 'foo:bar'));
        $request->setOriginalRequestMappingResults($result);

        $controllerContext = new ControllerContext();
        $controllerContext->setRequest($request);

        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);

        $javaScriptCode = FormRequestDataJavaScriptAssetHandler::with($assetHandlerFactory)
            ->getFormRequestDataJavaScriptCode();

        $this->assertEquals(
            $expectedResult,
            md5($this->removeMultiLinesComments($this->trimString($javaScriptCode)))
        );

        unset($formObject);
        unset($controllerContext);
    }
}
