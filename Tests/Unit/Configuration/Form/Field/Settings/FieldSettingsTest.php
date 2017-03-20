<?php
namespace Romm\Formz\Tests\Unit\Configuration\Field\Settings;

use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Configuration\Form\Field\Settings\FieldSettings;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class FieldSettingsTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function setFieldContainerSelectorSetsFieldContainerSelector()
    {
        $settings = new FieldSettings;
        $field = new Field;
        $field->setName('foo');
        $settings->setParents([$field]);

        $settings->setFieldContainerSelector('bar-' . FieldSettings::FIELD_MARKER);
        $this->assertEquals('bar-foo', $settings->getFieldContainerSelector());
    }

    /**
     * @test
     */
    public function setMessageContainerSelectorSetsMessageContainerSelector()
    {
        $settings = new FieldSettings;
        $field = new Field;
        $field->setName('foo');
        $settings->setParents([$field]);

        $settings->setMessageContainerSelector('bar-' . FieldSettings::FIELD_MARKER);
        $this->assertEquals('bar-foo', $settings->getMessageContainerSelector());
    }

    /**
     * @test
     */
    public function setMessageListSelectorSetsMessageListSelector()
    {
        $settings = new FieldSettings;
        $field = new Field;
        $field->setName('foo');
        $settings->setParents([$field]);

        $settings->setMessageListSelector('bar-' . FieldSettings::FIELD_MARKER);
        $this->assertEquals('bar-foo', $settings->getMessageListSelector());
    }

    /**
     * @test
     */
    public function setMessageTemplateSetsMessageTemplate()
    {
        $settings = new FieldSettings;
        $field = new Field;
        $field->setName('foo');
        $settings->setParents([$field]);

        $settings->setMessageTemplate('bar-' . FieldSettings::FIELD_MARKER);
        $this->assertEquals('bar-foo', $settings->getMessageTemplate());
    }

    /**
     * @test
     */
    public function settingsAreFetchedFromDefaultSettings()
    {
        $settings = new FieldSettings;

        $configuration = new Configuration;
        $defaultSettings = $configuration->getSettings()->getDefaultFieldSettings();

        $field = new Field;
        $field->setName('foo');

        $settings->setParents([$configuration, $field]);

        $defaultSettings->setFieldContainerSelector('field-container-selector-' . FieldSettings::FIELD_MARKER);
        $defaultSettings->setMessageContainerSelector('message-container-selector-' . FieldSettings::FIELD_MARKER);
        $defaultSettings->setMessageListSelector('message-list-selector-' . FieldSettings::FIELD_MARKER);
        $defaultSettings->setMessageTemplate('message-template-' . FieldSettings::FIELD_MARKER);

        $this->assertEquals('field-container-selector-foo', $settings->getFieldContainerSelector());
        $this->assertEquals('message-container-selector-foo', $settings->getMessageContainerSelector());
        $this->assertEquals('message-list-selector-foo', $settings->getMessageListSelector());
        $this->assertEquals('message-template-foo', $settings->getMessageTemplate());
    }
}
