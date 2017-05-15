<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\JavaScript\FieldsValidationActivationJavaScriptAssetHandler;
use Romm\Formz\Condition\Parser\Tree\ConditionTree;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\AssetHandler\AssetHandlerTestTrait;
use Romm\Formz\Validation\Validator\RequiredValidator;

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
(function(){Fz.Form.get('foo',function(form){varfield=null;field=form.getFieldByName('foo');if(null!==field){field.addActivationConditionForValidator('__auto','validation-name',function(field,continueValidation){varflag=false;flag=flag||(JAVASCRIPT-CONDITION);continueValidation(flag);});}form.refreshAllFields();});})();
TXT;

        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance();

        $field = $assetHandlerFactory->getFormObject()->getDefinition()->getField('foo');
        $field->addValidator('validation-name', RequiredValidator::class);

        /** @var FieldsValidationActivationJavaScriptAssetHandler|\PHPUnit_Framework_MockObject_MockObject $assetHandler */
        $assetHandler = $this->getMockBuilder(FieldsValidationActivationJavaScriptAssetHandler::class)
            ->setMethods(['getConditionTreeForValidator'])
            ->setConstructorArgs([$assetHandlerFactory])
            ->getMock();

        $assetHandler->expects($this->once())
            ->method('getConditionTreeForValidator')
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
