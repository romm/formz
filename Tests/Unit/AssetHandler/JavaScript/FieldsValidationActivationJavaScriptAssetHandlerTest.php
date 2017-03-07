<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\JavaScript\FieldsValidationActivationJavaScriptAssetHandler;
use Romm\Formz\Condition\Parser\ConditionTree;
use Romm\Formz\Configuration\Form\Field\Validation\Validation;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\AssetHandler\AssetHandlerTestTrait;

class FieldsValidationActivationJavaScriptAssetHandlerTest extends AbstractUnitTest
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
(function(){Formz.Form.get('foo',function(form){varfield=null;field=form.getFieldByName('foo');if(null!==field){field.addActivationConditionForValidator('__auto','validation-name',function(field,continueValidation){varflag=false;flag=flag||(JAVASCRIPT-CONDITION);continueValidation(flag);});}form.refreshAllFields();});})();
TXT;

        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance();

        $field = $assetHandlerFactory->getFormObject()->getConfiguration()->getField('foo');
        $validation = new Validation;
        $validation->setValidationName('validation-name');
        $field->addValidation($validation);

        /** @var FieldsValidationActivationJavaScriptAssetHandler|\PHPUnit_Framework_MockObject_MockObject $assetHandler */
        $assetHandler = $this->getMockBuilder(FieldsValidationActivationJavaScriptAssetHandler::class)
            ->setMethods(['getConditionTreeForValidation'])
            ->setConstructorArgs([$assetHandlerFactory])
            ->getMock();

        $assetHandler->expects($this->once())
            ->method('getConditionTreeForValidation')
            ->willReturnCallback(function () {
                $tree = $this->getMockBuilder(ConditionTree::class)
                    ->disableOriginalConstructor()
                    ->setMethods(['getJavaScriptConditions'])
                    ->getMock();

                $tree->expects($this->once())
                    ->method('getJavaScriptConditions')
                    ->willReturn(['JAVASCRIPT-CONDITION']);

                return $tree;
            });

        $javaScriptCode = $assetHandler->getFieldsValidationActivationJavaScriptCode();

        $this->assertEquals(
            $expectedResult,
            $this->removeMultiLinesComments($this->trimString($javaScriptCode))
        );

        unset($assetHandlerFactory);
    }
}
