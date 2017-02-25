<?php
/*
 * 2017 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Formz project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\Configuration\Form;

use Romm\ConfigurationObject\ConfigurationObjectInterface;
use Romm\ConfigurationObject\Service\Items\Parents\ParentsTrait;
use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\Traits\ConfigurationObject\ArrayConversionTrait;
use Romm\ConfigurationObject\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;
use Romm\ConfigurationObject\Traits\ConfigurationObject\StoreArrayIndexTrait;
use Romm\Formz\Configuration\AbstractFormzConfiguration;
use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Configuration\Form\Condition\ConditionItemResolver;
use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Configuration\Form\Settings\FormSettings;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Form extends AbstractFormzConfiguration implements ConfigurationObjectInterface
{
    use DefaultConfigurationObjectTrait;
    use StoreArrayIndexTrait;
    use ParentsTrait;
    use ArrayConversionTrait;

    /**
     * @var \ArrayObject<Romm\Formz\Configuration\Form\Field\Field>
     * @validate NotEmpty
     */
    protected $fields = [];

    /**
     * @var \ArrayObject<Romm\Formz\Configuration\Form\Condition\ConditionItemResolver>
     */
    protected $activationCondition = [];

    /**
     * @var \Romm\Formz\Configuration\Form\Settings\FormSettings
     */
    protected $settings;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->settings = GeneralUtility::makeInstance(FormSettings::class);
    }

    /**
     * Will initialize correctly the configuration object settings.
     *
     * @return ServiceFactory
     */
    public static function getConfigurationObjectServices()
    {
        return Configuration::getConfigurationObjectServices();
    }

    /**
     * Returns the root configuration object of Formz.
     *
     * @return Configuration
     */
    public function getFormzConfiguration()
    {
        return $this->getFirstParent(Configuration::class);
    }

    /**
     * @return Field[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param string $fieldName
     * @return bool
     */
    public function hasField($fieldName)
    {
        return true === isset($this->fields[$fieldName]);
    }

    /**
     * @param string $fieldName
     * @return Field|null
     */
    public function getField($fieldName)
    {
        $result = null;

        if ($this->hasField($fieldName)) {
            $result = $this->fields[$fieldName];
        }

        return $result;
    }

    /**
     * @param Field $field
     */
    public function addField(Field $field)
    {
        $this->fields[$field->getFieldName()] = $field;
    }

    /**
     * @return ConditionItemResolver[]
     */
    public function getActivationCondition()
    {
        return $this->activationCondition;
    }

    /**
     * @return FormSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }
}
