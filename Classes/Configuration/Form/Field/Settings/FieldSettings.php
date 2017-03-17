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

namespace Romm\Formz\Configuration\Form\Field\Settings;

use Romm\ConfigurationObject\Service\Items\Parents\ParentsTrait;
use Romm\Formz\Configuration\AbstractFormzConfiguration;
use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Configuration\Form\Field\Field;

class FieldSettings extends AbstractFormzConfiguration
{
    use ParentsTrait;

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
    protected $feedbackContainerSelector;

    /**
     * CSS selector to get the block element which will contain all the error
     * messages. It must be a child element of `$errorContainerSelector`. If
     * the value is empty, then `$errorContainerSelector` is considered to be
     * both the container and the block element.
     *
     * @var string
     */
    protected $feedbackListSelector;

    /**
     * @var string
     */
    protected $messageTemplate;

    /**
     * @param string $fieldName
     * @return string
     */
    public function getFieldContainerSelector($fieldName = null)
    {
        return $this->formatSelector($this->getSettingsProperty('fieldContainerSelector'), $fieldName);
    }

    /**
     * @param string $selector
     */
    public function setFieldContainerSelector($selector)
    {
        $this->fieldContainerSelector = $selector;
    }

    /**
     * @param null $fieldName
     * @return string
     */
    public function getFeedbackContainerSelector($fieldName = null)
    {
        return $this->formatSelector($this->getSettingsProperty('feedbackContainerSelector'), $fieldName);
    }

    /**
     * @param string $selector
     */
    public function setFeedbackContainerSelector($selector)
    {
        $this->feedbackContainerSelector = $selector;
    }

    /**
     * @param string $fieldName
     * @return string
     */
    public function getFeedbackListSelector($fieldName = null)
    {
        return $this->formatSelector($this->getSettingsProperty('feedbackListSelector'), $fieldName);
    }

    /**
     * @param string $fieldName
     * @return string
     */
    public function getMessageTemplate($fieldName = null)
    {
        return $this->formatSelector($this->getSettingsProperty('messageTemplate'), $fieldName);
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
     * @param string $fieldName
     * @return string
     */
    protected function formatSelector($selector, $fieldName = null)
    {
        if (null === $fieldName) {
            $fieldName = $this->getFieldName();
        }

        return str_replace('#FIELD#', $fieldName, $selector);
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

        if (null === $result) {
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
                return $field->getFieldName();
            }
        );
    }
}
