<?php
namespace Romm\Formz\Tests\Unit\Condition\Items;

use Romm\Formz\Condition\Items\AbstractConditionItem;
use Romm\Formz\Form\Definition\FormDefinition;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class AbstractConditionItemTest extends AbstractUnitTest
{
    /**
     * The validation of the condition configuration should be called only once.
     *
     * @test
     */
    public function conditionConfigurationIsValidatedOnce()
    {
        /** @var AbstractConditionItem|\PHPUnit_Framework_MockObject_MockObject $conditionItem */
        $conditionItem = $this->getMockBuilder(AbstractConditionItem::class)
            ->setMethods(['checkConditionConfiguration'])
            ->getMockForAbstractClass();

        $conditionItem->expects($this->once())
            ->method('checkConditionConfiguration');

        /** @var FormDefinition $formDefinition */
        $formDefinition = $this->getMockBuilder(FormDefinition::class)
            ->disableOriginalConstructor()
            ->getMock();

        $conditionItem->validateConditionConfiguration($formDefinition);
        $conditionItem->validateConditionConfiguration($formDefinition);
        $conditionItem->validateConditionConfiguration($formDefinition);
    }
}
