<?php
namespace Romm\Formz\Tests\Unit\Form\Definition\Condition;

use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Form\Definition\FormDefinition;
use Romm\Formz\Form\Definition\Settings\FormSettings;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class FormSettingsTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function setDefaultClassSetsDefaultClass()
    {
        $formSettings = new FormSettings;

        $formSettings->setDefaultClass('foo-bar');
        $this->assertEquals('foo-bar', $formSettings->getDefaultClass());
    }

    /**
     * @test
     */
    public function setDefaultClassOnFrozenDefinitionIsChecked()
    {
        /** @var FormSettings|\PHPUnit_Framework_MockObject_MockObject $formSettings */
        $formSettings = $this->getMockBuilder(FormSettings::class)
            ->setMethods(['checkDefinitionFreezeState'])
            ->getMock();

        $formSettings->expects($this->once())
            ->method('checkDefinitionFreezeState');

        $formSettings->setDefaultClass('foo');
    }

    /**
     * @test
     */
    public function defaultClassIsFetchedFromParent()
    {
        $formSettings = new FormSettings;
        $rootConfiguration = new Configuration;
        $formSettings->setParents([$rootConfiguration, new FormDefinition]);

        $rootConfiguration->getSettings()->getDefaultFormSettings()->setDefaultClass('bar-baz');

        $this->assertEquals('bar-baz', $formSettings->getDefaultClass());
    }

    /**
     * @test
     */
    public function setDefaultErrorMessageSetsDefaultErrorMessage()
    {
        $formSettings = new FormSettings;

        $formSettings->setDefaultErrorMessage('hello.world');
        $this->assertEquals('LLL::hello.world', $formSettings->getDefaultErrorMessage());
    }

    /**
     * @test
     */
    public function setDefaultErrorMessageOnFrozenDefinitionIsChecked()
    {
        /** @var FormSettings|\PHPUnit_Framework_MockObject_MockObject $formSettings */
        $formSettings = $this->getMockBuilder(FormSettings::class)
            ->setMethods(['checkDefinitionFreezeState'])
            ->getMock();

        $formSettings->expects($this->once())
            ->method('checkDefinitionFreezeState');

        $formSettings->setDefaultErrorMessage('foo');
    }

    /**
     * @test
     */
    public function defaultErrorMessageIsFetchedFromParent()
    {
        $formSettings = new FormSettings;
        $rootConfiguration = new Configuration;
        $formSettings->setParents([$rootConfiguration, new FormDefinition]);

        $rootConfiguration->getSettings()->getDefaultFormSettings()->setDefaultErrorMessage('world.hello');

        $this->assertEquals('LLL::LLL::world.hello', $formSettings->getDefaultErrorMessage());
    }
}
