<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\Css;

use Romm\Formz\AssetHandler\Css\FieldsActivationCssAssetHandler;
use Romm\Formz\Condition\Parser\Tree\ConditionTree;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\AssetHandler\AssetHandlerTestTrait;

class FieldsActivationCssAssetHandlerTest extends AbstractUnitTest
{
    use AssetHandlerTestTrait;

    /**
     * Checks that the CSS code returned by the asset handler is valid.
     *
     * @test
     */
    public function fieldsActivationCssIsValid()
    {
        $expectedCss = 'form[name="foo"][fz-field-container="foo"]{display:none;}form[name="foo"]CSS-CONDITION[fz-field-container="foo"]{display:block;}';

        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance();

        /** @var FieldsActivationCssAssetHandler|\PHPUnit_Framework_MockObject_MockObject $assetHandler */
        $assetHandler = $this->getMockBuilder(FieldsActivationCssAssetHandler::class)
            ->setMethods(['getConditionTreeForField'])
            ->setConstructorArgs([$assetHandlerFactory])
            ->getMock();

        $assetHandler->expects($this->once())
            ->method('getConditionTreeForField')
            ->willReturnCallback(function () {
                $tree = $this->getMockBuilder(ConditionTree::class)
                    ->disableOriginalConstructor()
                    ->setMethods(['getCssConditions'])
                    ->getMock();

                $tree->expects($this->once())
                    ->method('getCssConditions')
                    ->willReturn(['CSS-CONDITION']);

                return $tree;
            });

        $fieldsActivationCss = $assetHandler->getFieldsActivationCss();

        $this->assertEquals($expectedCss, $this->removeMultiLinesComments($this->trimString($fieldsActivationCss)));

        unset($assetHandlerFactory);
    }
}
