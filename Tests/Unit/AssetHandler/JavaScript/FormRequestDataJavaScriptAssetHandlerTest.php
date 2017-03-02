<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\JavaScript\FormRequestDataJavaScriptAssetHandler;
use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Error\Error;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\AssetHandler\AssetHandlerTestTrait;
use TYPO3\CMS\Extbase\Error\Result;
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
        $expectedResult = <<<TXT
(function(){Formz.Form.beforeInitialization('foo',function(form){form.injectRequestData({"foo":"foo"},{"foo":{"errors":{"foo":{"bar":"error"}}}},true,["foo"])});})();
TXT;

        $originalRequest = new Request;
        $originalRequest->setArgument('foo', ['foo' => 'foo']);

        $request = new Request;
        $request->setOriginalRequest($originalRequest);

        $result = new Result;
        $formResult = new FormResult;

        $field = new Field;
        $field->setFieldName('foo');
        $formResult->deactivateField($field);

        $error = new Error(
            'error',
            42,
            'foo',
            'bar',
            [],
            ''
        );

        $formResult->forProperty('foo')->addError($error);
        $result->forProperty('foo')
            ->merge($formResult);

        $request->setOriginalRequestMappingResults($result);

        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance(DefaultForm::class);
        $assetHandlerFactory->getControllerContext()->setRequest($request);

        /** @var FormRequestDataJavaScriptAssetHandler $formRequestDataJavaScriptAssetHandler */
        $formRequestDataJavaScriptAssetHandler = $assetHandlerFactory->getAssetHandler(FormRequestDataJavaScriptAssetHandler::class);
        $formRequestDataJavaScriptAssetHandler->getFormObject()
            ->setLastValidationResult($formResult);

        $javaScriptCode = $formRequestDataJavaScriptAssetHandler->getFormRequestDataJavaScriptCode();

        $this->assertEquals(
            $expectedResult,
            $this->removeMultiLinesComments($this->trimString($javaScriptCode))
        );

        unset($assetHandlerFactory);
    }
}
