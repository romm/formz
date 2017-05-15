<?php
namespace Romm\Formz\Tests\Unit\Form\Definition;

use Romm\Formz\Behaviours\ToLowerCaseBehaviour;
use Romm\Formz\Form\Definition\Field\Behaviour\Behaviour;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class BehaviourTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function initializationDoneProperly()
    {
        $behaviourName = 'my-behaviour';
        $behaviourClassName = ToLowerCaseBehaviour::class;
        $behaviour = new Behaviour($behaviourName, $behaviourClassName);

        $this->assertSame($behaviourName, $behaviour->getName());
        $this->assertSame($behaviourClassName, $behaviour->getClassName());
    }
}
