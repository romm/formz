<?php
namespace Romm\Formz\Tests\Fixture\Form\Definition;

use Romm\Formz\Form\Definition\AbstractFormDefinitionComponent;

class DummyDefinition extends AbstractFormDefinitionComponent
{
    /**
     * @var string
     */
    protected $foo;

    /**
     * Methods that calls the definition free state check.
     */
    public function dummyCheckDefinitionFreezeState()
    {
        $this->checkDefinitionFreezeState();
    }
}
