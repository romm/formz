<?php
namespace Romm\Formz\Tests\Unit\Form\Definition\Field\Settings;

use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\Definition\Field\Settings\FieldSettings;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class FieldSettingsTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function setFieldContainerSelectorSetsFieldContainerSelector()
    {
        $settings = new FieldSettings;
        $field = new Field('foo');
        $settings->attachParent($field);

        $settings->setFieldContainerSelector('bar-' . FieldSettings::FIELD_MARKER);
        $this->assertEquals('bar-foo', $settings->getFieldContainerSelector());
    }

    /**
     * @test
     */
    public function setFieldContainerSelectorOnFrozenDefinitionIsChecked()
    {
        $settings = $this->getFieldSettingsWithDefinitionFreezeStateCheck();

        $settings->setFieldContainerSelector('bar-' . FieldSettings::FIELD_MARKER);
    }

    /**
     * @test
     */
    public function setMessageContainerSelectorSetsMessageContainerSelector()
    {
        $settings = new FieldSettings;
        $field = new Field('foo');
        $settings->attachParent($field);

        $settings->setMessageContainerSelector('bar-' . FieldSettings::FIELD_MARKER);
        $this->assertEquals('bar-foo', $settings->getMessageContainerSelector());
    }

    /**
     * @test
     */
    public function setMessageContainerSelectorOnFrozenDefinitionIsChecked()
    {
        $settings = $this->getFieldSettingsWithDefinitionFreezeStateCheck();

        $settings->setMessageContainerSelector('bar-' . FieldSettings::FIELD_MARKER);
    }

    /**
     * @test
     */
    public function setMessageListSelectorSetsMessageListSelector()
    {
        $settings = new FieldSettings;
        $field = new Field('foo');
        $settings->attachParent($field);

        $settings->setMessageListSelector('bar-' . FieldSettings::FIELD_MARKER);
        $this->assertEquals('bar-foo', $settings->getMessageListSelector());
    }

    /**
     * @test
     */
    public function setMessageListSelectorOnFrozenDefinitionIsChecked()
    {
        $settings = $this->getFieldSettingsWithDefinitionFreezeStateCheck();

        $settings->setMessageListSelector('bar-' . FieldSettings::FIELD_MARKER);
    }

    /**
     * @test
     */
    public function setMessageTemplateSetsMessageTemplate()
    {
        $settings = new FieldSettings;
        $field = new Field('foo');
        $settings->attachParent($field);

        $settings->setMessageTemplate('bar-' . FieldSettings::FIELD_MARKER);
        $this->assertEquals('bar-foo', $settings->getMessageTemplate());
    }

    /**
     * @test
     */
    public function setMessageTemplateOnFrozenDefinitionIsChecked()
    {
        $settings = $this->getFieldSettingsWithDefinitionFreezeStateCheck();

        $settings->setMessageTemplate('bar-' . FieldSettings::FIELD_MARKER);
    }

    /**
     * @test
     */
    public function settingsAreFetchedFromDefaultSettings()
    {
        $settings = new FieldSettings;

        $configuration = new Configuration;
        $defaultSettings = $configuration->getSettings()->getDefaultFieldSettings();

        $field = new Field('foo');

        $settings->attachParents([$configuration, $field]);

        $defaultSettings->setFieldContainerSelector('field-container-selector-' . FieldSettings::FIELD_MARKER);
        $defaultSettings->setMessageContainerSelector('message-container-selector-' . FieldSettings::FIELD_MARKER);
        $defaultSettings->setMessageListSelector('message-list-selector-' . FieldSettings::FIELD_MARKER);
        $defaultSettings->setMessageTemplate('message-template-' . FieldSettings::FIELD_MARKER);

        $this->assertEquals('field-container-selector-foo', $settings->getFieldContainerSelector());
        $this->assertEquals('message-container-selector-foo', $settings->getMessageContainerSelector());
        $this->assertEquals('message-list-selector-foo', $settings->getMessageListSelector());
        $this->assertEquals('message-template-foo', $settings->getMessageTemplate());
    }

    /**
     * @return FieldSettings|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFieldSettingsWithDefinitionFreezeStateCheck()
    {
        /** @var FieldSettings|\PHPUnit_Framework_MockObject_MockObject $fieldSettings */
        $fieldSettings = $this->getMockBuilder(FieldSettings::class)
            ->setMethods(['checkDefinitionFreezeState'])
            ->getMock();

        $fieldSettings->expects($this->once())
            ->method('checkDefinitionFreezeState');

        return $fieldSettings;
    }
}
