<?php
namespace Romm\Formz\Tests\Unit\Form\Definition\Condition;

use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesResolver;
use Romm\Formz\Condition\ConditionFactory;
use Romm\Formz\Condition\Items\ConditionItemInterface;
use Romm\Formz\Form\Definition\Condition\ConditionItemResolver;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class ConditionItemResolverTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function conditionIsFound()
    {
        $conditionItem = $this->getMockBuilder(ConditionItemInterface::class)
            ->getMockForAbstractClass();
        $conditionClass = get_class($conditionItem);

        ConditionFactory::get()->registerCondition('foo', $conditionClass);

        $resolver = new MixedTypesResolver;
        $resolver->setData(['type' => 'foo']);

        ConditionItemResolver::getInstanceClassName($resolver);

        $this->assertEquals($conditionClass, $resolver->getObjectType());
        $this->assertFalse($resolver->getResult()->hasErrors());
    }

    /**
     * @test
     */
    public function conditionIsNotFound()
    {
        $resolver = new MixedTypesResolver;
        $resolver->setData(['type' => 'nope']);

        ConditionItemResolver::getInstanceClassName($resolver);

        $this->assertTrue($resolver->getResult()->hasErrors());
    }
}
