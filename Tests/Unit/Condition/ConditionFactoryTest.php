<?php
namespace Romm\Formz\Tests\Unit\Condition;

use Romm\Formz\Condition\ConditionFactory;
use Romm\Formz\Condition\Items\FieldHasValueCondition;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Exceptions\InvalidArgumentTypeException;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class ConditionFactoryTest extends AbstractUnitTest
{

    /**
     * @test
     */
    public function registerConditionMustBeAString()
    {
        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionCode(1466588489);

        $conditionFactory = new ConditionFactory;
        $conditionFactory->registerCondition(true, 'foo');
    }

    /**
     * @test
     */
    public function registerConditionWithInvalidClassNameThrowsException()
    {
        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionCode(1466588495);

        $conditionFactory = new ConditionFactory;
        $conditionFactory->registerCondition('foo', \stdClass::class);
    }

    /**
     * Registering a new valid condition should work, and the getter methods
     * should then be accessible.
     *
     * @test
     */
    public function registerConditionWorks()
    {
        $conditionName = 'foo';
        $conditionClassName = FieldHasValueCondition::class;

        $conditionFactory = new ConditionFactory;
        $conditionFactory->registerCondition($conditionName, $conditionClassName);
        $this->assertTrue($conditionFactory->hasCondition($conditionName));
        $this->assertEquals($conditionClassName, $conditionFactory->getCondition($conditionName));
    }

    /**
     * Trying to access an unregistered condition must throw an exception.
     *
     * @test
     */
    public function getUnregisteredConditionThrowsException()
    {
        $this->expectException(EntryNotFoundException::class);

        $conditionFactory = new ConditionFactory;
        $conditionFactory->getCondition('bar');
    }

    /**
     * The default conditions registration method should be called only once.
     *
     * @test
     */
    public function defaultConditionMustBeRegisteredOnlyOnce()
    {
        $defaultConditionsWereRegistered = false;

        /** @var ConditionFactory|\PHPUnit_Framework_MockObject_MockObject $conditionFactoryMock */
        $conditionFactoryMock = $this->getMockBuilder(ConditionFactory::class)
            ->setMethods(['registerCondition'])
            ->getMock();

        $conditionFactoryMock->expects($this->atLeastOnce())
            ->method('registerCondition')
            ->willReturnCallback(function() use (&$defaultConditionsWereRegistered, &$conditionFactoryMock) {
                $this->assertFalse($defaultConditionsWereRegistered);

                return $conditionFactoryMock;
            });

        $conditionFactoryMock->registerDefaultConditions();
        /** @noinspection PhpUnusedLocalVariableInspection */
        $defaultConditionsWereRegistered = true;
        $conditionFactoryMock->registerDefaultConditions();
    }
}
