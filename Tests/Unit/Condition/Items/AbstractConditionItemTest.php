<?php
namespace Romm\Formz\Tests\Unit\Condition\Items;

use Romm\Formz\Condition\Exceptions\InvalidConditionException;
use Romm\Formz\Condition\Items\AbstractConditionItem;
use Romm\Formz\Condition\Parser\Node\ConditionNode;
use Romm\Formz\Configuration\Form\Field\Activation\Activation;
use Romm\Formz\Configuration\Form\Field\Validation\Validation;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class AbstractConditionItemTest extends AbstractUnitTest
{
    /**
     * An exception throw during validation of a field condition item must be
     * catch and thrown back with a custom message.
     *
     * @test
     */
    public function validatingFieldConditionConfigurationThrowsException()
    {
        $this->setExpectedException(InvalidConditionException::class, null, 1488653398);

        /** @var AbstractConditionItem|\PHPUnit_Framework_MockObject_MockObject $conditionItem */
        $conditionItem = $this->getMockBuilder(AbstractConditionItem::class)
            ->setMethods(['checkConditionConfiguration'])
            ->getMockForAbstractClass();

        $conditionItem->expects($this->once())
            ->method('checkConditionConfiguration')
            ->willThrowException(new InvalidConditionException('foo', 42));

        $formObject = $this->getDefaultFormObject();

        $field = $formObject->getConfiguration()->getField('foo');

        $activation = new Activation;
        $activation->setRootObject($field);

        $conditionNode = new ConditionNode('foo', $conditionItem);

        $conditionItem->attachFormObject($formObject);
        $conditionItem->attachActivation($activation);
        $conditionItem->attachConditionNode($conditionNode);

        $conditionItem->validateConditionConfiguration();
    }

    /**
     * An exception throw during validation of a validation condition item must
     * be catch and thrown back with a custom message.
     *
     * @test
     */
    public function validatingValidationConditionConfigurationThrowsException()
    {
        $this->setExpectedException(InvalidConditionException::class, null, 1488653713);

        /** @var AbstractConditionItem|\PHPUnit_Framework_MockObject_MockObject $conditionItem */
        $conditionItem = $this->getMockBuilder(AbstractConditionItem::class)
            ->setMethods(['checkConditionConfiguration'])
            ->getMockForAbstractClass();

        $conditionItem->expects($this->once())
            ->method('checkConditionConfiguration')
            ->willThrowException(new InvalidConditionException('foo', 42));

        $formObject = $this->getDefaultFormObject();

        $field = $formObject->getConfiguration()->getField('foo');

        $validation = new Validation;
        $validation->setParents([$field]);

        $activation = new Activation;
        $activation->setRootObject($validation);

        $conditionNode = new ConditionNode('foo', $conditionItem);

        $conditionItem->attachFormObject($formObject);
        $conditionItem->attachActivation($activation);
        $conditionItem->attachConditionNode($conditionNode);

        $conditionItem->validateConditionConfiguration();
    }
}
