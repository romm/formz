<?php
namespace Romm\Formz\Tests\Unit\Configuration\View;

use Romm\Formz\Configuration\Settings\Settings;
use Romm\Formz\Form\Definition\Field\Settings\FieldSettings;
use Romm\Formz\Form\Definition\Settings\FormSettings;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class SettingsTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function initializationDoneProperly()
    {
        $settings = new Settings;

        $this->assertInstanceOf(FormSettings::class, $settings->getDefaultFormSettings());
        $this->assertInstanceOf(FieldSettings::class, $settings->getDefaultFieldSettings());
    }

    /**
     * @test
     */
    public function setDefaultBackendCacheSetsDefaultBackendCache()
    {
        $settings = new Settings;

        $settings->setDefaultBackendCache('foo');
        $this->assertEquals('foo', $settings->getDefaultBackendCache());
    }

    /**
     * @test
     */
    public function setDefaultBackendCacheOnFrozenConfigurationIsChecked()
    {
        $settings = $this->getSettingsWithConfigurationFreezeStateCheck();

        $settings->setDefaultBackendCache('foo');
    }

    /**
     * @return Settings|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSettingsWithConfigurationFreezeStateCheck()
    {
        /** @var Settings|\PHPUnit_Framework_MockObject_MockObject $settings */
        $settings = $this->getMockBuilder(Settings::class)
            ->setConstructorArgs(['foo'])
            ->setMethods(['checkConfigurationFreezeState'])
            ->getMock();

        $settings->expects($this->once())
            ->method('checkConfigurationFreezeState');

        return $settings;
    }
}
