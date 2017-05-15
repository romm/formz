<?php
namespace Romm\Formz\Tests\Unit\Configuration;

use Romm\Formz\Configuration\ConfigurationState;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class ConfigurationStateTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function markAsFrozenMarksAsFrozen()
    {
        $state = new ConfigurationState;

        $this->assertFalse($state->isFrozen());
        $state->markAsFrozen();
        $this->assertTrue($state->isFrozen());
    }
}
