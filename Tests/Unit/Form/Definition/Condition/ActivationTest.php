<?php
namespace Romm\Formz\Tests\Unit\Form\Definition\Condition;

use Romm\Formz\Condition\ConditionFactory;
use Romm\Formz\Condition\Items\ConditionItemInterface;
use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Form\Definition\Condition\Activation;
use Romm\Formz\Form\Definition\Condition\ActivationUsageInterface;
use Romm\Formz\Form\Definition\FormDefinition;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\UnitTestContainer;

class ActivationTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function setExpressionSetsExpression()
    {
        $activation = new Activation;

        $activation->setExpression('expression');
        $this->assertEquals('expression', $activation->getExpression());
    }

    /**
     * @test
     */
    public function setExpressionOnFrozenDefinitionIsChecked()
    {
        /** @var Activation|\PHPUnit_Framework_MockObject_MockObject $activation */
        $activation = $this->getMockBuilder(Activation::class)
            ->setMethods(['checkDefinitionFreezeState'])
            ->getMock();

        $activation->expects($this->once())
            ->method('checkDefinitionFreezeState');

        $activation->setExpression('expression');
    }

    /**
     * @test
     */
    public function addConditionAddsCondition()
    {
        $conditionName = 'foo';
        $conditionIdentifier = 'bar';
        $conditionArguments = ['foo' => 'bar'];

        $conditionFactoryMock = $this->getConditionFactoryMock();

        $conditionFactoryMock->expects($this->once())
            ->method('hasCondition')
            ->with($conditionIdentifier);

        $conditionFactoryMock->expects($this->once())
            ->method('instantiateCondition')
            ->with($conditionIdentifier, $conditionArguments);

        $activation = new Activation;
        $this->assertFalse($activation->hasCondition($conditionName));
        $condition = $activation->addCondition('foo', $conditionIdentifier, $conditionArguments);
        $this->assertTrue($activation->hasCondition($conditionName));
        $this->assertSame($condition, $activation->getCondition($conditionName));
        $this->assertSame([$conditionName => $condition], $activation->getConditions());
    }

    /**
     * @test
     */
    public function addConditionOnFrozenDefinitionIsChecked()
    {
        $conditionIdentifier = 'bar';

        /** @var Activation|\PHPUnit_Framework_MockObject_MockObject $activation */
        $activation = $this->getMockBuilder(Activation::class)
            ->setMethods(['checkDefinitionFreezeState'])
            ->getMock();

        $activation->expects($this->once())
            ->method('checkDefinitionFreezeState');

        $conditionFactoryMock = $this->getConditionFactoryMock();

        $conditionFactoryMock->expects($this->once())
            ->method('hasCondition')
            ->with($conditionIdentifier);

        $activation->addCondition('foo', $conditionIdentifier);
    }

    /**
     * @test
     */
    public function addExistingConditionThrowsException()
    {
        $this->setExpectedException(DuplicateEntryException::class);

        $this->getConditionFactoryMock();

        $activation = new Activation;
        $activation->addCondition('foo', 'foo');
        $activation->addCondition('foo', 'foo');
    }

    /**
     * @test
     */
    public function addUnregisteredConditionThrowsException()
    {
        $this->setExpectedException(EntryNotFoundException::class);

        $conditionFactoryMock = $this->getMockBuilder(ConditionFactory::class)
            ->setMethods(['hasCondition'])
            ->getMock();

        $conditionFactoryMock->method('hasCondition')
            ->willReturn(false);

        UnitTestContainer::get()->registerMockedInstance(ConditionFactory::class, $conditionFactoryMock);

        $activation = new Activation;
        $activation->addCondition('foo', 'foo');
    }

    /**
     * @test
     */
    public function getUnknownConditionThrowsException()
    {
        $this->setExpectedException(EntryNotFoundException::class);

        $activation = new Activation;
        $activation->getCondition('nope');
    }

    /**
     * @test
     */
    public function conditionAreMergedWithFormConfiguration()
    {
        $condition1Identifier = 'condition1Identifier';
        $condition1Arguments = ['foo' => 'bar'];
        $condition2Identifier = 'condition2Identifier';
        $condition2Arguments = ['bar' => 'baz'];

        $conditionFactoryMock = $this->getConditionFactoryMock();

        $conditionFactoryMock->expects($this->exactly(2))
            ->method('hasCondition')
            ->withConsecutive([$condition1Identifier], [$condition2Identifier]);

        $conditionFactoryMock->expects($this->exactly(2))
            ->method('instantiateCondition')
            ->withConsecutive(
                [$condition1Identifier, $condition1Arguments],
                [$condition2Identifier, $condition2Arguments]
            );

        $form = new FormDefinition;
        $activation = new Activation;
        $activation->attachParent($form);

        $this->assertFalse($activation->hasCondition('foo'));
        $this->assertFalse($activation->hasCondition('bar'));

        $condition1 = $form->addCondition('foo', $condition1Identifier, $condition1Arguments);
        $condition2 = $activation->addCondition('bar', $condition2Identifier, $condition2Arguments);

        $this->assertTrue($activation->hasCondition('foo'));
        $this->assertTrue($activation->hasCondition('bar'));

        $this->assertSame($condition1, $activation->getCondition('foo'));
        $this->assertSame($condition2, $activation->getCondition('bar'));

        $this->assertSame(
            [
                'foo' => $condition1,
                'bar' => $condition2
            ],
            $activation->getAllConditions()
        );
    }

    /**
     * @test
     */
    public function conditionAreOverridingFormConfiguration()
    {
        $condition1Identifier = 'condition1Identifier';
        $condition1Arguments = ['foo' => 'bar'];
        $condition2Identifier = 'condition2Identifier';
        $condition2Arguments = ['bar' => 'baz'];

        $conditionFactoryMock = $this->getConditionFactoryMock();

        $conditionFactoryMock->expects($this->exactly(2))
            ->method('hasCondition')
            ->withConsecutive([$condition1Identifier], [$condition2Identifier]);

        $conditionFactoryMock->expects($this->exactly(2))
            ->method('instantiateCondition')
            ->withConsecutive(
                [$condition1Identifier, $condition1Arguments],
                [$condition2Identifier, $condition2Arguments]
            );

        $form = new FormDefinition;
        $activation = new Activation;
        $activation->attachParent($form);

        $this->assertFalse($activation->hasCondition('foo'));

        $condition1 = $form->addCondition('foo', $condition1Identifier, $condition1Arguments);

        $this->assertTrue($activation->hasCondition('foo'));
        $this->assertSame($condition1, $activation->getCondition('foo'));

        $condition2 = $activation->addCondition('foo', $condition2Identifier, $condition2Arguments);

        $this->assertTrue($activation->hasCondition('foo'));
        $this->assertSame($condition2, $activation->getCondition('foo'));

        $this->assertSame(
            ['foo' => $condition2],
            $activation->getAllConditions()
        );
    }

    /**
     * @test
     */
    public function getRootObjectReturnsRootObject()
    {
        $rootObject = $this->prophesize(ActivationUsageInterface::class)->reveal();

        $activation = new Activation;
        $activation->attachParent($rootObject);

        $this->assertSame($rootObject, $activation->getRootObject());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getConditionFactoryMock()
    {
        $conditionFactoryMock = $this->getMockBuilder(ConditionFactory::class)
            ->setMethods(['hasCondition', 'instantiateCondition'])
            ->getMock();

        $conditionFactoryMock->method('hasCondition')
            ->willReturn(true);

        $conditionFactoryMock->method('instantiateCondition')
            ->willReturnCallback(function () {
                return $this->prophesize(ConditionItemInterface::class)->reveal();
            });

        UnitTestContainer::get()->registerMockedInstance(ConditionFactory::class, $conditionFactoryMock);

        return $conditionFactoryMock;
    }
}
