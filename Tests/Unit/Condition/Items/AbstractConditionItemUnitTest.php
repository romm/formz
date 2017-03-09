<?php
namespace Romm\Formz\Tests\Unit\Condition\Items;

use Romm\Formz\Condition\Exceptions\InvalidConditionException;
use Romm\Formz\Condition\Items\ConditionItemInterface;
use Romm\Formz\Form\FormObject;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

abstract class AbstractConditionItemUnitTest extends AbstractUnitTest
{
    /**
     * @param $className
     * @param $errorCode
     * @return ConditionItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getConditionItemWithFailedConfigurationValidation($className, $errorCode)
    {
        $conditionItem = $this->getConditionItem($className);

        $conditionItem->expects($this->once())
            ->method('throwInvalidConditionException')
            ->willReturnCallback(function ($exception) use ($errorCode) {
                /** @var InvalidConditionException $exception */
                $this->assertInstanceOf(InvalidConditionException::class, $exception);
                $this->assertEquals($errorCode, $exception->getCode());
            });

        return $conditionItem;
    }

    /**
     * @param string     $className
     * @param FormObject $formObject
     * @return \PHPUnit_Framework_MockObject_MockObject|ConditionItemInterface
     */
    protected function getConditionItemWithValidConfigurationValidation($className, FormObject $formObject = null)
    {
        $conditionItem = $this->getConditionItem($className, $formObject);

        $conditionItem->expects($this->never())
            ->method('throwInvalidConditionException');

        return $conditionItem;
    }

    /**
     * @param string     $className
     * @param FormObject $formObject
     * @return \PHPUnit_Framework_MockObject_MockObject|ConditionItemInterface
     */
    private function getConditionItem($className, FormObject $formObject = null)
    {
        /** @var ConditionItemInterface|\PHPUnit_Framework_MockObject_MockObject $conditionItem */
        $conditionItem = $this->getMockBuilder($className)
            ->setMethods(['throwInvalidConditionException'])
            ->getMock();

        $formObject = $formObject ?: $this->getDefaultFormObject();
        $conditionItem->attachFormObject($formObject);

        return $conditionItem;
    }
}
