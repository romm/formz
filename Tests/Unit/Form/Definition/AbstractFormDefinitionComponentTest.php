<?php
namespace Romm\Formz\Tests\Unit\Form\Definition;

use Romm\Formz\Configuration\ConfigurationState;
use Romm\Formz\Exceptions\PropertyNotAccessibleException;
use Romm\Formz\Form\Definition\FormDefinition;
use Romm\Formz\Tests\Fixture\Form\Definition\DummyDefinition;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class AbstractFormDefinitionComponentTest extends AbstractUnitTest
{
    /**
     * No exception should be thrown when the definition is not frozen.
     *
     * @test
     */
    public function checkNotFrozenDefinitionWorks()
    {
        $formDefinitionMock = $this->getMockBuilder(FormDefinition::class)
            ->setMethods(['getState'])
            ->getMock();

        $formDefinitionState = $this->getFormDefinitionState(false);
        $formDefinitionMock->expects($this->atLeastOnce())
            ->method('getState')
            ->willReturn($formDefinitionState);

        $componentDefinition = new DummyDefinition;
        $componentDefinition->attachParent($formDefinitionMock);
        $componentDefinition->dummyCheckDefinitionFreezeState();
    }

    /**
     * When the definition is frozen, an exception must be thrown.
     *
     * @test
     */
    public function checkFrozenDefinitionThrowsException()
    {
        $this->setExpectedException(PropertyNotAccessibleException::class);

        $formDefinitionMock = $this->getMockBuilder(FormDefinition::class)
            ->setMethods(['getState'])
            ->getMock();

        $formDefinitionState = $this->getFormDefinitionState(true);
        $formDefinitionMock->expects($this->atLeastOnce())
            ->method('getState')
            ->willReturn($formDefinitionState);

        $componentDefinition = new DummyDefinition;
        $componentDefinition->attachParent($formDefinitionMock);
        $componentDefinition->dummyCheckDefinitionFreezeState();
    }

    /**
     * A magic setter method from the magic method trait should throw an
     * exception when not used by the Configuration Object API.
     *
     * @test
     */
    public function magicSetterMethodThrowsException()
    {
        $this->setExpectedException(PropertyNotAccessibleException::class);

        $componentDefinition = new DummyDefinition;
        $componentDefinition->setFoo('bar');
    }

    /**
     * @param bool $frozen
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFormDefinitionState($frozen)
    {
        $formDefinitionState = $this->getMockBuilder(ConfigurationState::class)
            ->setMethods(['isFrozen'])
            ->getMock();

        $formDefinitionState->expects($this->atLeastOnce())
            ->method('isFrozen')
            ->willReturn($frozen);

        return $formDefinitionState;
    }
}
