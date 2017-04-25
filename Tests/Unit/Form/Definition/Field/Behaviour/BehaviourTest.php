<?php
namespace Romm\Formz\Tests\Unit\Form\Definition;

use Romm\Formz\Behaviours\BehaviourInterface;
use Romm\Formz\Form\Definition\Field\Behaviour\Behaviour;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class BehaviourTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function setClassNamesSetsClassName()
    {
        $behaviour = new Behaviour;

        $behaviourInstance = $this->getMockBuilder(BehaviourInterface::class)
            ->getMockForAbstractClass();
        $behaviourClass = get_class($behaviourInstance);

        $behaviour->setClassName($behaviourClass);
        $this->assertEquals($behaviourClass, $behaviour->getClassName());
    }
}
