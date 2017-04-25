<?php
namespace Romm\Formz\Tests\Unit\Condition\Parser;

use Romm\Formz\Condition\Parser\ConditionParserFactory;
use Romm\Formz\Form\Definition\Field\Activation\Activation;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class ConditionParserFactoryTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function parsedTreeIsStoredInLocalCache()
    {
        /** @var ConditionParserFactory|\PHPUnit_Framework_MockObject_MockObject $conditionParserFactory */
        $conditionParserFactory = $this->getMockBuilder(ConditionParserFactory::class)
            ->setMethods(['getConditionTree'])
            ->getMock();

        $conditionParserFactory->expects($this->exactly(2))
            ->method('getConditionTree');

        $fooActivation = new Activation;
        $fooActivation->setExpression('foo');

        $conditionParserFactory->parse($fooActivation);
        $conditionParserFactory->parse($fooActivation);
        $conditionParserFactory->parse($fooActivation);

        $barActivation = new Activation;
        $barActivation->setExpression('bar');

        $conditionParserFactory->parse($barActivation);
        $conditionParserFactory->parse($barActivation);
        $conditionParserFactory->parse($barActivation);
    }

    /**
     * @test
     */
    public function parsedTreeIsStoredInPersistentCache()
    {
        /** @var ConditionParserFactory|\PHPUnit_Framework_MockObject_MockObject $conditionParserFactory */
        $conditionParserFactory = $this->getMockBuilder(ConditionParserFactory::class)
            ->setMethods(['buildConditionTree'])
            ->getMock();

        $fooActivation = new Activation;
        $fooActivation->setExpression('foo');

        $fooTree = new \stdClass;
        $fooTree->foo = 'foo';

        $conditionParserFactory->expects($this->at(0))
            ->method('buildConditionTree')
            ->with($fooActivation)
            ->willReturn($fooTree);

        $barActivation = new Activation;
        $barActivation->setExpression('bar');

        $barTree = new \stdClass;
        $barTree->bar = 'bar';

        $conditionParserFactory->expects($this->at(1))
            ->method('buildConditionTree')
            ->with($barActivation)
            ->willReturn($barTree);

        $conditionParserFactory->expects($this->exactly(2))
            ->method('buildConditionTree');

        $conditionParserFactory->parse($fooActivation);
        $conditionParserFactory->parse($barActivation);

        /** @var ConditionParserFactory|\PHPUnit_Framework_MockObject_MockObject $conditionParserFactoryBis */
        $conditionParserFactoryBis = $this->getMockBuilder(ConditionParserFactory::class)
            ->setMethods(['buildConditionTree'])
            ->getMock();

        $conditionParserFactoryBis->expects($this->never())
            ->method('buildConditionTree');

        $fooTreeResult = $conditionParserFactoryBis->parse($fooActivation);
        $this->assertEquals($fooTree, $fooTreeResult);

        $barTreeResult = $conditionParserFactoryBis->parse($barActivation);
        $this->assertEquals($barTree, $barTreeResult);
    }
}
