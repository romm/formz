<?php
namespace Romm\Formz\Tests\Unit\Form\Definition;

use Romm\Formz\Form\Definition\FormDefinitionState;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class FormDefinitionStateTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function markDefinitionAsFrozenMarksDefinitionAsFrozen()
    {
        $state = new FormDefinitionState;

        $this->assertFalse($state->isDefinitionFrozen());
        $state->markDefinitionAsFrozen();
        $this->assertTrue($state->isDefinitionFrozen());
    }
}
