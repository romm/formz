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

namespace Romm\Formz\Form\Definition;

use Romm\ConfigurationObject\ConfigurationObjectInterface;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorInterface;
use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\Traits\ConfigurationObject\ArrayConversionTrait;
use Romm\ConfigurationObject\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;
use Romm\Formz\Condition\ConditionFactory;
use Romm\Formz\Condition\Items\ConditionItemInterface;
use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Configuration\ConfigurationState;
use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\Definition\Middleware\PresetMiddlewares;
use Romm\Formz\Form\Definition\Settings\FormSettings;
use Romm\Formz\Middleware\MiddlewareFactory;
use Romm\Formz\Middleware\MiddlewareInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FormDefinition extends AbstractFormDefinitionComponent implements ConfigurationObjectInterface, DataPreProcessorInterface
{
    use DefaultConfigurationObjectTrait;
    use ArrayConversionTrait;

    /**
     * @var \Romm\Formz\Form\Definition\Field\Field[]
     * @validate NotEmpty
     */
    protected $fields = [];

    /**
     * @var \Romm\Formz\Condition\Items\ConditionItemInterface[]
     * @mixedTypesResolver \Romm\Formz\Form\Definition\Condition\ConditionItemResolver
     */
    protected $conditionList = [];

    /**
     * @var \Romm\Formz\Form\Definition\Settings\FormSettings
     */
    protected $settings;

    /**
     * @var \Romm\Formz\Form\Definition\Middleware\PresetMiddlewares
     */
    protected $presetMiddlewares;

    /**
     * @var \Romm\Formz\Middleware\MiddlewareInterface[]
     * @mixedTypesResolver \Romm\Formz\Form\Definition\Middleware\MiddlewareResolver
     */
    protected $middlewares = [];

    /**
     * @var ConfigurationState
     */
    private $state;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->settings = GeneralUtility::makeInstance(FormSettings::class);
        $this->state = GeneralUtility::makeInstance(ConfigurationState::class);
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
     * @param string $name
     * @return Field
     * @throws DuplicateEntryException
     */
    public function addField($name)
    {
        $this->checkDefinitionFreezeState();

        if ($this->hasField($name)) {
            throw DuplicateEntryException::fieldAlreadyAdded($name);
        }

        /** @var Field $field */
        $field = GeneralUtility::makeInstance(Field::class, $name);
        $field->attachParent($this);

        $this->fields[$name] = $field;

        return $field;
    }

    /**
     * @return ConditionItemInterface[]
     */
    public function getConditionList()
    {
        return $this->conditionList;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasCondition($name)
    {
        return true === isset($this->conditionList[$name]);
    }

    /**
     * @param string $name
     * @return ConditionItemInterface
     * @throws EntryNotFoundException
     */
    public function getCondition($name)
    {
        if (false === $this->hasCondition($name)) {
            throw EntryNotFoundException::conditionNotFoundInDefinition($name);
        }

        return $this->conditionList[$name];
    }

    /**
     * @param string $name
     * @param string $identifier
     * @param array  $arguments
     * @return ConditionItemInterface
     * @throws DuplicateEntryException
     */
    public function addCondition($name, $identifier, array $arguments = [])
    {
        $this->checkDefinitionFreezeState();

        if ($this->hasCondition($name)) {
            throw DuplicateEntryException::formConditionAlreadyAdded($name);
        }

        $this->conditionList[$name] = $this->createCondition($identifier, $arguments);

        return $this->conditionList[$name];
    }

    /**
     * @param string $identifier
     * @param array  $arguments
     * @return ConditionItemInterface
     * @throws EntryNotFoundException
     */
    protected function createCondition($identifier, array $arguments = [])
    {
        $conditionFactory = ConditionFactory::get();

        if (false === $conditionFactory->hasCondition($identifier)) {
            throw EntryNotFoundException::formAddConditionNotFound($identifier, $conditionFactory->getConditions());
        }

        $condition = $conditionFactory->instantiateCondition($identifier, $arguments);
        $condition->attachParent($this);

        return $condition;
    }

    /**
     * @return FormSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return PresetMiddlewares
     */
    public function getPresetMiddlewares()
    {
        return $this->presetMiddlewares;
    }

    /**
     * @return MiddlewareInterface[]
     */
    public function getMiddlewares()
    {
        return $this->middlewares;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasMiddleware($name)
    {
        return isset($this->middlewares[$name]);
    }

    /**
     * @param string $name
     * @return MiddlewareInterface
     * @throws EntryNotFoundException
     */
    public function getMiddleware($name)
    {
        if (false === $this->hasMiddleware($name)) {
            throw EntryNotFoundException::middlewareNotFound($name);
        }

        return $this->middlewares[$name];
    }

    /**
     * @param string $name
     * @param string $className
     * @param callable $optionsCallback
     * @return MiddlewareInterface
     * @throws DuplicateEntryException
     */
    public function addMiddleware($name, $className, callable $optionsCallback = null)
    {
        $this->checkDefinitionFreezeState();

        if ($this->hasMiddleware($name)) {
            throw new DuplicateEntryException('@todo');  // @todo
        }

        $this->middlewares[$name] = MiddlewareFactory::get()->create($className, $optionsCallback);

        return $this->middlewares[$name];
    }

    /**
     * Returns the merged list of preset middlewares and custom registered
     * middlewares.
     *
     * @return MiddlewareInterface[]
     */
    public function getAllMiddlewares()
    {
        $middlewaresList = $this->middlewares;

        foreach ($this->presetMiddlewares->getList() as $name => $middleware) {
            $middlewaresList['__preset-' . $name] = $middleware;
        }

        return $middlewaresList;
    }

    /**
     * @return ConfigurationState
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param DataPreProcessor $processor
     */
    public static function dataPreProcessor(DataPreProcessor $processor)
    {
        $data = $processor->getData();

        if (false === isset($data['presetMiddlewares'])) {
            $data['presetMiddlewares'] = [];
        }

        /*
         * Forcing the names of the fields: they are the keys of the array
         * entries.
         */
        self::forceNameForProperty($data, 'fields');

        $processor->setData($data);
    }
}
