<?php
namespace Romm\Formz\Tests\Unit\Condition\Parser\Node;

use Romm\Formz\Condition\Exceptions\InvalidConditionException;
use Romm\Formz\Condition\Items\ConditionItemInterface;
use Romm\Formz\Condition\Parser\Node\ConditionNode;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use Romm\Formz\Form\Definition\Condition\ActivationUsageInterface;
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
        /** @var ConditionItemInterface|\PHPUnit_Framework_MockObject_MockObject $condition */
        $condition = $this->getMockBuilder(ConditionItemInterface::class)
            ->setMethods(['getPhpResult'])
            ->getMockForAbstractClass();

        $condition->expects($this->once())
            ->method('getPhpResult');

        /** @var ConditionNode|\PHPUnit_Framework_MockObject_MockObject $node */
        $node = $this->getMockBuilder(ConditionNode::class)
            ->setMethods(['checkConditionConfiguration'])
            ->setConstructorArgs(['foo', $condition])
            ->getMock();

        $node->expects($this->once())
            ->method('checkConditionConfiguration');

        $node->getPhpResult($this->getPhpConditionDataObjectMock());
    }

    /**
     * Checks that the condition configuration is validated before the php
     * process is launched.
     *
     * @test
     */
    public function conditionConfigurationIsValidated()
    {
        /** @var ConditionItemInterface|\PHPUnit_Framework_MockObject_MockObject $condition */
        $condition = $this->getMockBuilder(ConditionItemInterface::class)
            ->setMethods(['validateConditionConfiguration', 'getPhpResult'])
            ->getMockForAbstractClass();

        $formObject = $this->getDefaultFormObject();

        $condition->expects($this->once())
            ->method('validateConditionConfiguration')
            ->with($formObject->getDefinition());

        /** @var ConditionNode|\PHPUnit_Framework_MockObject_MockObject $node */
        $node = $this->getMockBuilder(ConditionNode::class)
            ->setMethods(['getFormObject'])
            ->setConstructorArgs(['foo', $condition])
            ->getMock();

        $node->method('getFormObject')
            ->willReturn($formObject);

        $node->getPhpResult($this->getPhpConditionDataObjectMock());
    }

    /**
     * @test
     */
    public function conditionConfigurationValidationExceptionIsThrown()
    {
        $this->setExpectedException(InvalidConditionException::class);

        /** @var ConditionItemInterface|\PHPUnit_Framework_MockObject_MockObject $condition */
        $condition = $this->getMockBuilder(ConditionItemInterface::class)
            ->setMethods(['validateConditionConfiguration', 'getPhpResult'])
            ->getMockForAbstractClass();

        $formObject = $this->getDefaultFormObject();

        $condition->expects($this->once())
            ->method('validateConditionConfiguration')
            ->with($formObject->getDefinition())
            ->willReturnCallback(function () {
                throw new InvalidConditionException('foo', 42);
            });

        /** @var ConditionNode|\PHPUnit_Framework_MockObject_MockObject $node */
        $node = $this->getMockBuilder(ConditionNode::class)
            ->setMethods(['getFormObject', 'getRootObject'])
            ->setConstructorArgs(['foo', $condition])
            ->getMock();

        $node->method('getFormObject')
            ->willReturn($formObject);

        $node->method('getRootObject')
            ->willReturn($this->getMockBuilder(ActivationUsageInterface::class)->getMockForAbstractClass());

        $node->getPhpResult($this->getPhpConditionDataObjectMock());
    }

    /**
     * @return PhpConditionDataObject
     */
    protected function getPhpConditionDataObjectMock()
    {
        /** @var PhpConditionDataObject $phpConditionDataObject */
        $phpConditionDataObject = $this->getMockBuilder(PhpConditionDataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $phpConditionDataObject;
    }
}
