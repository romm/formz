<?php
/*
 * 2017 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 FormZ project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\Form\Definition\Field\Settings;

use Romm\ConfigurationObject\Service\Items\Parents\ParentsTrait;
use Romm\Formz\Configuration\AbstractFormzConfiguration;
use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Form\Definition\Field\Field;

class FieldSettings extends AbstractFormzConfiguration
{
    use ParentsTrait;

    const FIELD_MARKER = '#FIELD#';

    /**
     * CSS selector to get the container of the field.
     *
     * @var string
     */
    protected $fieldContainerSelector;

    /**
     * CSS selector to get the error container of the parent field.
     *
     * @var string
     */
    protected $messageContainerSelector;

    /**
     * CSS selector to get the block element which will contain all the
     * messages. It must be a child element of `$messageContainerSelector`. If
     * the value is empty, then `$messageContainerSelector` is considered to be
     * both the container and the block element.
     *
     * @var string
     */
    protected $messageListSelector;

    /**
     * @var string
     */
    protected $messageTemplate;

    /**
     * @return string
     */
    public function getFieldContainerSelector()
    {
        return $this->formatSelector($this->getSettingsProperty('fieldContainerSelector'));
    }

    /**
     * @param string $selector
     */
    public function setFieldContainerSelector($selector)
    {
        $this->fieldContainerSelector = $selector;
    }

    /**
     * @return string
     */
    public function getMessageContainerSelector()
    {
        return $this->formatSelector($this->getSettingsProperty('messageContainerSelector'));
    }

    /**
     * @param string $selector
     */
    public function setMessageContainerSelector($selector)
    {
        $this->messageContainerSelector = $selector;
    }

    /**
     * @return string
     */
    public function getMessageListSelector()
    {
        return $this->formatSelector($this->getSettingsProperty('messageListSelector'));
    }

    /**
     * @param string $messageListSelector
     */
    public function setMessageListSelector($messageListSelector)
    {
        $this->messageListSelector = $messageListSelector;
    }

    /**
     * @return string
     */
    public function getMessageTemplate()
    {
        return $this->formatSelector($this->getSettingsProperty('messageTemplate'));
    }

    /**
     * @param string $messageTemplate
     */
    public function setMessageTemplate($messageTemplate)
    {
        $this->messageTemplate = $messageTemplate;
    }

    /**
     * @param string $selector
     * @return string
     */
    protected function formatSelector($selector)
    {
        $fieldName = $this->getFieldName();

        if ($fieldName) {
            $selector = str_replace(self::FIELD_MARKER, $fieldName, $selector);
        }

        return $selector;
    }

    /**
     * This function will do the following: first, it will check if the wanted
     * property is set in this class instance (not null), then it returns it. If
     * the value is null, it will fetch the global FormZ configuration settings,
     * and return the default value for the asked property.
     *
     * Example:
     *  config.tx_formz.forms.My\Custom\Form.fields.myField.settings.fieldContainerSelector is null, then
     *  config.tx_formz.settings.defaultFieldSettings.fieldContainerSelector is returned
     *
     * @param string $propertyName Name of the wanted class property.
     * @return mixed|null
     */
    private function getSettingsProperty($propertyName)
    {
        $result = $this->$propertyName;

        if (empty($result)) {
            $result = $this->withFirstParent(
                Configuration::class,
                function (Configuration $configuration) use ($propertyName) {
                    $fieldName = $this->getFieldName();
                    $getter = 'get' . ucfirst($propertyName);

                    return $configuration->getSettings()->getDefaultFieldSettings()->$getter($fieldName);
                }
            );
        }

        return $result;
    }

    /**
     * @return string
     */
    private function getFieldName()
    {
        return $this->withFirstParent(
            Field::class,
            function (Field $field) {
                return $field->getName();
            }
        );
    }
}
