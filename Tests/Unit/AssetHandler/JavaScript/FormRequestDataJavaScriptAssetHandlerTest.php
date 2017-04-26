<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\JavaScript\FormRequestDataJavaScriptAssetHandler;
use Romm\Formz\Error\Error;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Form\FormObject\FormObjectProxy;
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
(function(){Fz.Form.beforeInitialization('foo',function(form){form.injectRequestData({"foo":"foo"},{"foo":{"errors":{"foo":{"bar":"error"}}}},true,["foo"])});})();
TXT;

        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance();
        $formObjectMock = $this->getFormObjectMock();

        /** @var FormRequestDataJavaScriptAssetHandler|\PHPUnit_Framework_MockObject_MockObject $assetHandler */
        $assetHandler = $this->getMockBuilder(FormRequestDataJavaScriptAssetHandler::class)
            ->setConstructorArgs([$assetHandlerFactory])
            ->setMethods(['getFormObject'])
            ->getMock();

        $assetHandler->method('getFormObject')
            ->willReturn($formObjectMock);

        $originalRequest = new Request;
        $originalRequest->setArgument('foo', ['foo' => 'foo']);

        $request = new Request;
        $request->setOriginalRequest($originalRequest);

        $result = new Result;
        $formResult = $formObjectMock->getFormResult();

        $field = $formObjectMock->getDefinition()->getField('foo');
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

        $assetHandlerFactory->getControllerContext()->setRequest($request);

        $javaScriptCode = $assetHandler->getFormRequestDataJavaScriptCode();

        $this->assertEquals(
            $expectedResult,
            $this->removeMultiLinesComments($this->trimString($javaScriptCode))
        );

        unset($assetHandlerFactory);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FormObject
     */
    protected function getFormObjectMock()
    {
        /** @var FormObject|\PHPUnit_Framework_MockObject_MockObject $formObjectMock */
        $formObjectMock = $this->getMockBuilder(FormObject::class)
            ->setConstructorArgs(['foo', $this->getDefaultFormObjectStatic()])
            ->setMethods(['formWasSubmitted', 'getProxy'])
            ->getMock();

        $formObjectMock->method('formWasSubmitted')
            ->willReturn(true);

        $formObjectMock->method('getProxy')
            ->willReturn(new FormObjectProxy($formObjectMock, new DefaultForm));

        return $formObjectMock;
    }
}
