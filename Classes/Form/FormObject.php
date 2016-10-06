<?php
/*
 * 2016 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Formz project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\Form;

use Romm\ConfigurationObject\ConfigurationObjectInstance;
use Romm\ConfigurationObject\ConfigurationObjectFactory;
use Romm\Formz\Configuration\Form\Form;
use Romm\Formz\Core\Core;

/**
 * This is the object representation of a form. In here we can manage which
 * properties the form does have, its configuration, and more.
 */
class FormObject
{

    /**
     * Name of the form.
     *
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $className;

    /**
     * The properties of the form.
     *
     * @var array
     */
    protected $properties = [];

    /**
     * Contains the form configuration.
     *
     * @var array
     */
    protected $configurationArray = [];

    /**
     * Contains the form configuration object, which was created from the
     * configuration array.
     *
     * @var ConfigurationObjectInstance
     */
    protected $configurationObject;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var bool
     */
    protected $hashShouldBeCalculated = true;

    /**
     * You should never create a new instance of this class directly, use the
     * `FormObjectFactory->getInstanceFromClassName()` function instead.
     *
     * @param string $className
     * @param string $name
     */
    public function __construct($className, $name)
    {
        $this->className = $className;
        $this->name = $name;
    }

    /**
     * Registers a new property for this form.
     *
     * @param string $name
     * @return $this
     */
    public function addProperty($name)
    {
        if (false === in_array($name, $this->properties)) {
            $this->properties[] = $name;
            $this->hashShouldBeCalculated = true;
        }

        return $this;
    }

    /**
     * @return Form
     */
    public function getConfiguration()
    {
        /** @var Form $configuration */
        $configuration = $this->getConfigurationObject()->getObject(true);

        return $configuration;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Returns the hash, which should be calculated only once for performance
     * concerns.
     *
     * @return string
     */
    public function getHash()
    {
        if (true === $this->hashShouldBeCalculated
            || null === $this->hash
        ) {
            $this->hashShouldBeCalculated = false;
            $this->hash = $this->calculateHash();
        }

        return $this->hash;
    }

    /**
     * Returns an instance of configuration object. Checks if it was previously
     * stored in cache, otherwise it is created from scratch.
     *
     * @return ConfigurationObjectInstance
     * @internal
     */
    public function getConfigurationObject()
    {
        if (null === $this->configurationObject) {
            $cacheInstance = Core::get()->getCacheInstance();
            $cacheIdentifier = 'configuration-' . $this->getHash();

            if ($cacheInstance->has($cacheIdentifier)) {
                $configurationObject = $cacheInstance->get($cacheIdentifier);
            } else {
                $configurationObject =  ConfigurationObjectFactory::getInstance()
                    ->get(Form::class, $this->configurationArray);

                if (false === $configurationObject->getValidationResult()->hasErrors()) {
                    $cacheInstance->set($cacheIdentifier, $configurationObject);
                }
            }

            $this->configurationObject = $configurationObject;
        }

        return $this->configurationObject;
    }

    /**
     * This function will clean the configuration array by removing useless data
     * and updating needed ones.
     *
     * @param array $configuration
     * @return array
     */
    protected function sanitizeConfiguration(array $configuration)
    {
        // Removing configuration of fields which do not exist for this form.
        $sanitizedFieldsConfiguration = [];
        $fieldsConfiguration = (isset($configuration['fields']))
            ? $configuration['fields']
            : [];

        foreach ($this->properties as $property) {
            $sanitizedFieldsConfiguration[$property] = (isset($fieldsConfiguration[$property]))
                ? $fieldsConfiguration[$property]
                : [];
        }

        $configuration['fields'] = $sanitizedFieldsConfiguration;

        return $configuration;
    }

    /**
     * Returns the calculated hash of this class.
     *
     * @return string
     */
    protected function calculateHash()
    {
        return sha1(serialize($this));
    }

    /**
     * When this instance is saved in TYPO3 cache, we need not to store all the
     * properties to increase performance.
     *
     * @return array
     */
    public function __sleep()
    {
        return ['name', 'className', 'properties', 'configurationArray', 'hash'];
    }

    /**
     * When this class is unserialized, we update the flag to know if the hash
     * should be calculated or not (if it was calculated before it was
     * serialized, there is no need to calculate it again).
     */
    public function __wakeup()
    {
        $this->hashShouldBeCalculated = (null === $this->hash);
    }

    /**
     * @return array
     * @internal Should not be used, it is here only for unit tests.
     */
    public function getConfigurationArray()
    {
        return $this->configurationArray;
    }

    /**
     * @param array $configuration
     * @return $this
     */
    public function setConfigurationArray($configuration)
    {
        $this->configurationArray = $this->sanitizeConfiguration($configuration);
        $this->hashShouldBeCalculated = true;

        return $this;
    }
}
