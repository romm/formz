<?php
namespace Romm\Formz\Tests\Unit\Condition\Parser\Node;

use Romm\Formz\Condition\Items\ConditionItemInterface;
use Romm\Formz\Condition\Parser\Node\ConditionNode;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class ConditionNodeTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function getCssResult()
    {
        /** @var ConditionItemInterface|\PHPUnit_Framework_MockObject_MockObject $condition */
        $condition = $this->getMockBuilder(ConditionItemInterface::class)
            ->setMethods(['getCssResult'])
            ->getMockForAbstractClass();

        $condition->expects($this->once())
            ->method('getCssResult');

        $node = new ConditionNode('foo', $condition);
        $node->getCssResult();
    }

    /**
     * @test
     */
    public function getJavaScriptResult()
    {
        /** @var ConditionItemInterface|\PHPUnit_Framework_MockObject_MockObject $condition */
        $condition = $this->getMockBuilder(ConditionItemInterface::class)
            ->setMethods(['getJavaScriptResult'])
            ->getMockForAbstractClass();

        $condition->expects($this->once())
            ->method('getJavaScriptResult');

        $node = new ConditionNode('foo', $condition);
        $node->getJavaScriptResult();
    }

    /**
     * @test
     */
    public function getPhpResult()
    {
        /** @var PhpConditionDataObject $phpConditionDataObject */
        $phpConditionDataObject = $this->getMockBuilder(PhpConditionDataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var ConditionItemInterface|\PHPUnit_Framework_MockObject_MockObject $condition */
        $condition = $this->getMockBuilder(ConditionItemInterface::class)
            ->setMethods(['validateConditionConfiguration', 'getPhpResult'])
            ->getMockForAbstractClass();

        $condition->expects($this->once())
            ->method('validateConditionConfiguration');

        $condition->expects($this->once())
            ->method('getPhpResult');

        $node = new ConditionNode('foo', $condition);
        $node->getPhpResult($phpConditionDataObject);
    }
}
