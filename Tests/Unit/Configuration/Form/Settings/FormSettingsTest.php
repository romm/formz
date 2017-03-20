<?php
namespace Romm\Formz\Tests\Unit\Configuration\Condition;

use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Configuration\Form\Form;
use Romm\Formz\Configuration\Form\Settings\FormSettings;
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
    public function defaultClassIsFetchedFromParent()
    {
        $formSettings = new FormSettings;
        $rootConfiguration = new Configuration;
        $formSettings->setParents([$rootConfiguration, new Form]);

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
    public function defaultErrorMessageIsFetchedFromParent()
    {
        $formSettings = new FormSettings;
        $rootConfiguration = new Configuration;
        $formSettings->setParents([$rootConfiguration, new Form]);

        $rootConfiguration->getSettings()->getDefaultFormSettings()->setDefaultErrorMessage('world.hello');

        $this->assertEquals('LLL::LLL::world.hello', $formSettings->getDefaultErrorMessage());
    }
}
