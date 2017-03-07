<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\JavaScript\FieldsActivationJavaScriptAssetHandler;
use Romm\Formz\Condition\Parser\ConditionTree;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\AssetHandler\AssetHandlerTestTrait;

class FieldsActivationJavaScriptAssetHandlerTest extends AbstractUnitTest
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
(function(){Formz.Form.get('foo',function(form){varfield=null;field=form.getFieldByName('foo');if(null!==field){field.addActivationCondition('__auto',function(field,continueValidation){varflag=false;flag=flag||(JAVASCRIPT-CONDITION);continueValidation(flag);});}});})();
TXT;

        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance();

        /** @var FieldsActivationJavaScriptAssetHandler|\PHPUnit_Framework_MockObject_MockObject $assetHandler */
        $assetHandler = $this->getMockBuilder(FieldsActivationJavaScriptAssetHandler::class)
            ->setMethods(['getConditionTreeForField'])
            ->setConstructorArgs([$assetHandlerFactory])
            ->getMock();

        $assetHandler->expects($this->once())
            ->method('getConditionTreeForField')
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

        $javaScriptCode = $assetHandler->getFieldsActivationJavaScriptCode();

        $this->assertEquals(
            $expectedResult,
            $this->removeMultiLinesComments($this->trimString($javaScriptCode))
        );

        unset($assetHandlerFactory);
    }
}
