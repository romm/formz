<?php
namespace Romm\Formz\Tests\Unit\Form\Definition;

use Romm\Formz\Condition\ConditionFactory;
use Romm\Formz\Condition\Items\ConditionItemInterface;
use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Configuration\ConfigurationState;
use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Form\Definition\FormDefinition;
use Romm\Formz\Form\Definition\Settings\FormSettings;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\UnitTestContainer;

class FormDefinitionTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function initializationDoneProperly()
    {
        $formDefinition = new FormDefinition;
        $this->assertInstanceOf(FormSettings::class, $formDefinition->getSettings());
        $this->assertInstanceOf(ConfigurationState::class, $formDefinition->getState());
    }

    /**
     * @test
     */
    public function rootConfigurationIsFetched()
    {
        $formDefinition = new FormDefinition;
        $rootConfiguration = new Configuration;

        $formDefinition->attachParent($rootConfiguration);

        $this->assertSame($rootConfiguration, $formDefinition->getRootConfiguration());
    }

    /**
     * @test
     */
    public function addFieldAddsField()
    {
        $formDefinition = new FormDefinition;

        $this->assertFalse($formDefinition->hasField('foo'));
        $field = $formDefinition->addField('foo');
        $this->assertTrue($formDefinition->hasField('foo'));
        $this->assertSame($field, $formDefinition->getField('foo'));
        $this->assertSame(['foo' => $field], $formDefinition->getFields());
    }

    /**
     * @test
     */
    public function addFieldOnFrozenDefinitionIsChecked()
    {
        $formDefinition = $this->getFormDefinitionWithDefinitionFreezeStateCheck();

        $formDefinition->addField('foo');
    }

    /**
     * @test
     */
    public function addExistingFieldThrowsException()
    {
        $this->setExpectedException(DuplicateEntryException::class);

        $formDefinition = new FormDefinition;

        $formDefinition->addField('foo');
        $formDefinition->addField('foo');
    }

    /**
     * @test
     */
    public function getUnknownFieldThrowsException()
    {
        $this->setExpectedException(EntryNotFoundException::class);

        $formDefinition = new FormDefinition;
        $formDefinition->getField('nope');
    }

    /**
     * @test
     */
    public function addConditionAddsCondition()
    {
        $conditionName = 'foo';
        $conditionIdentifier = 'bar';
        $conditionArguments = ['foo' => 'bar'];

        $conditionFactoryMock = $this->getConditionFactoryMock();

        $conditionFactoryMock->expects($this->once())
            ->method('hasCondition')
            ->with($conditionIdentifier);

        $conditionFactoryMock->expects($this->once())
            ->method('instantiateCondition')
            ->with($conditionIdentifier, $conditionArguments);

        $formDefinition = new FormDefinition;

        $this->assertFalse($formDefinition->hasCondition($conditionName));
        $condition = $formDefinition->addCondition('foo', $conditionIdentifier, $conditionArguments);
        $this->assertTrue($formDefinition->hasCondition($conditionName));
        $this->assertSame($condition, $formDefinition->getCondition($conditionName));
        $this->assertSame([$conditionName => $condition], $formDefinition->getConditionList());
    }

    /**
     * @test
     */
    public function addConditionOnFrozenDefinitionIsChecked()
    {
        $this->getConditionFactoryMock();

        $formDefinition = $this->getFormDefinitionWithDefinitionFreezeStateCheck();

        $formDefinition->addCondition('foo', 'foo');
    }

    /**
     * @test
     */
    public function addExistingConditionThrowsException()
    {
        $this->setExpectedException(DuplicateEntryException::class);

        $formDefinition = new FormDefinition;

        $this->getConditionFactoryMock();

        $formDefinition->addCondition('foo', 'foo');
        $formDefinition->addCondition('foo', 'foo');
    }

    /**
     * @test
     */
    public function addNotRegisteredConditionThrowsException()
    {
        $this->setExpectedException(EntryNotFoundException::class);

        $conditionIdentifier = 'foo-condition';

        $conditionFactoryMock = $this->getMockBuilder(ConditionFactory::class)
            ->setMethods(['hasCondition'])
            ->getMock();

        UnitTestContainer::get()->registerMockedInstance(ConditionFactory::class, $conditionFactoryMock);

        $conditionFactoryMock->expects($this->once())
            ->method('hasCondition')
            ->with($conditionIdentifier)
            ->willReturn(false);

        $formDefinition = new FormDefinition;
        $formDefinition->addCondition('foo', $conditionIdentifier);
    }

    /**
     * @test
     */
    public function getUnknownConditionThrowsException()
    {
        $this->setExpectedException(EntryNotFoundException::class);

        $formDefinition = new FormDefinition;
        $formDefinition->getCondition('foo');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getConditionFactoryMock()
    {
        $conditionFactoryMock = $this->getMockBuilder(ConditionFactory::class)
            ->setMethods(['hasCondition', 'instantiateCondition'])
            ->getMock();

        $conditionFactoryMock->method('hasCondition')
            ->willReturn(true);

        $conditionFactoryMock->method('instantiateCondition')
            ->willReturnCallback(function () {
                return $this->prophesize(ConditionItemInterface::class)->reveal();
            });

        UnitTestContainer::get()->registerMockedInstance(ConditionFactory::class, $conditionFactoryMock);

        return $conditionFactoryMock;
    }

    /**
     * @return FormDefinition|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFormDefinitionWithDefinitionFreezeStateCheck()
    {
        /** @var FormDefinition|\PHPUnit_Framework_MockObject_MockObject $formDefinition */
        $formDefinition = $this->getMockBuilder(FormDefinition::class)
            ->setMethods(['checkDefinitionFreezeState'])
            ->getMock();

        $formDefinition->expects($this->once())
            ->method('checkDefinitionFreezeState');

        return $formDefinition;
    }
}
