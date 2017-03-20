<?php
namespace Romm\Formz\Tests\Unit\Configuration\Field\Activation;

use Romm\Formz\Condition\Items\ConditionItemInterface;
use Romm\Formz\Configuration\Form\Field\Activation\Activation;
use Romm\Formz\Configuration\Form\Field\Activation\ActivationUsageInterface;
use Romm\Formz\Configuration\Form\Form;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

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
    public function setRootObjectSetsRootObject()
    {
        $activation = new Activation;

        /** @var ActivationUsageInterface $conditionItem */
        $rootObject = $this->getMockBuilder(ActivationUsageInterface::class)
            ->getMockForAbstractClass();

        $activation->setRootObject($rootObject);
        $this->assertSame($rootObject, $activation->getRootObject());
    }

    /**
     * @test
     */
    public function addConditionAddsCondition()
    {
        $activation = new Activation;

        /** @var ConditionItemInterface $condition */
        $condition = $this->getMockBuilder(ConditionItemInterface::class)
            ->getMockForAbstractClass();

        $this->assertFalse($activation->hasCondition('foo'));
        $activation->addCondition('foo', $condition);
        $this->assertTrue($activation->hasCondition('foo'));
        $this->assertSame($condition, $activation->getCondition('foo'));
        $this->assertSame(['foo' => $condition], $activation->getConditions());
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
        $activation = new Activation;
        $form = new Form;
        $activation->setParents([$form]);

        /** @var ConditionItemInterface $condition1 */
        $condition1 = $this->getMockBuilder(ConditionItemInterface::class)
            ->getMockForAbstractClass();

        /** @var ConditionItemInterface $condition2 */
        $condition2 = $this->getMockBuilder(ConditionItemInterface::class)
            ->getMockForAbstractClass();

        $this->assertFalse($activation->hasCondition('foo'));
        $this->assertFalse($activation->hasCondition('bar'));

        $form->addCondition('foo', $condition1);
        $activation->addCondition('bar', $condition2);

        $this->assertTrue($activation->hasCondition('foo'));
        $this->assertTrue($activation->hasCondition('bar'));

        $this->assertSame($condition1, $activation->getCondition('foo'));
        $this->assertSame($condition2, $activation->getCondition('bar'));

        $this->assertSame(
            [
                'foo' => $condition1,
                'bar' => $condition2
            ],
            $activation->getConditions()
        );
    }

    /**
     * @test
     */
    public function conditionAreOverridingFormConfiguration()
    {
        $activation = new Activation;
        $form = new Form;
        $activation->setParents([$form]);

        /** @var ConditionItemInterface $condition1 */
        $condition1 = $this->getMockBuilder(ConditionItemInterface::class)
            ->getMockForAbstractClass();

        /** @var ConditionItemInterface $condition2 */
        $condition2 = $this->getMockBuilder(ConditionItemInterface::class)
            ->getMockForAbstractClass();

        $this->assertFalse($activation->hasCondition('foo'));

        $form->addCondition('foo', $condition1);
        $activation->addCondition('foo', $condition2);

        $this->assertTrue($activation->hasCondition('foo'));

        $this->assertSame($condition2, $activation->getCondition('foo'));

        $this->assertSame(
            ['foo' => $condition2],
            $activation->getConditions()
        );
    }
}
