<?php
namespace Romm\Formz\Tests\Unit\Configuration\View;

use Romm\Formz\Configuration\Form\Field\Settings\FieldSettings;
use Romm\Formz\Configuration\Form\Settings\FormSettings;
use Romm\Formz\Configuration\Settings\Settings;
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
}
