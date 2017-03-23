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

namespace Romm\Formz\Configuration\Form;

use Romm\ConfigurationObject\ConfigurationObjectInterface;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorInterface;
use Romm\ConfigurationObject\Service\Items\Parents\ParentsTrait;
use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\Traits\ConfigurationObject\ArrayConversionTrait;
use Romm\ConfigurationObject\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;
use Romm\ConfigurationObject\Traits\ConfigurationObject\StoreArrayIndexTrait;
use Romm\Formz\Condition\Items\ConditionItemInterface;
use Romm\Formz\Configuration\AbstractFormzConfiguration;
use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Configuration\Form\Settings\FormSettings;
use Romm\Formz\Exceptions\EntryNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Error;

class Form extends AbstractFormzConfiguration implements ConfigurationObjectInterface, DataPreProcessorInterface
{
    use DefaultConfigurationObjectTrait;
    use StoreArrayIndexTrait;
    use ParentsTrait;
    use ArrayConversionTrait;

    /**
     * @var \Romm\Formz\Configuration\Form\Field\Field[]
     * @validate NotEmpty
     */
    protected $fields = [];

    /**
     * @var ConditionItemInterface[]
     * @mixedTypesResolver \Romm\Formz\Configuration\Form\Condition\ConditionItemResolver
     */
    protected $conditionList = [];

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
     * Returns FormZ root configuration object.
     *
     * @return Configuration
     */
    public function getRootConfiguration()
    {
        /** @var Configuration $configuration */
        $configuration = $this->getFirstParent(Configuration::class);

        return $configuration;
    }

    /**
     * @return Field[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasField($name)
    {
        return true === isset($this->fields[$name]);
    }

    /**
     * @param string $name
     * @return Field
     * @throws EntryNotFoundException
     */
    public function getField($name)
    {
        if (false === $this->hasField($name)) {
            throw EntryNotFoundException::configurationFieldNotFound($name);
        }

        return $this->fields[$name];
    }

    /**
     * @param Field $field
     */
    public function addField(Field $field)
    {
        $field->setParents([$this]);

        $this->fields[$field->getName()] = $field;
    }

    /**
     * @return ConditionItemInterface[]
     */
    public function getConditionList()
    {
        return $this->conditionList;
    }

    /**
     * @param string                 $name
     * @param ConditionItemInterface $condition
     */
    public function addCondition($name, ConditionItemInterface $condition)
    {
        $this->conditionList[$name] = $condition;
    }

    /**
     * @return FormSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param DataPreProcessor $processor
     */
    public static function dataPreProcessor(DataPreProcessor $processor)
    {
        $data = $processor->getData();

        if (isset($data['activationCondition'])) {
            $error = new Error(
                'The property "activationCondition" has been deprecated and renamed to "conditionList", please change your TypoScript configuration.',
                1489763042
            );
            $processor->addError($error);
        }
    }
}
