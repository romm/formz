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

namespace Romm\Formz\Form;

use Romm\ConfigurationObject\ConfigurationObjectFactory;
use Romm\ConfigurationObject\ConfigurationObjectInstance;
use Romm\Formz\Configuration\ConfigurationFactory;
use Romm\Formz\Configuration\Form\Form;
use Romm\Formz\Core\Core;
use Romm\Formz\Service\CacheService;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Extbase\Error\Result;

class FormObjectConfiguration
{
    /**
     * @var FormObject
     */
    protected $formObject;

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
     * @var Result
     */
    protected $configurationValidationResult;

    /**
     * @var string
     */
    protected $lastConfigurationHash;

    /**
     * @var ConfigurationFactory
     */
    protected $configurationFactory;

    /**
     * @param FormObject $formObject
     * @param array      $configurationArray
     */
    public function __construct(FormObject $formObject, array $configurationArray)
    {
        $this->formObject = $formObject;
        $this->configurationArray = $configurationArray;
    }

    /**
     * Returns an instance of configuration object. Checks if it was previously
     * stored in cache, otherwise it is created from scratch.
     *
     * @return ConfigurationObjectInstance
     */
    public function getConfigurationObject()
    {
        if (null === $this->configurationObject
            || $this->lastConfigurationHash !== $this->formObject->getHash()
        ) {
            $this->lastConfigurationHash = $this->formObject->getHash();
            $this->configurationObject = $this->getConfigurationObjectFromCache();
        }

        return $this->configurationObject;
    }

    /**
     * This function will merge and return the validation results of both the
     * global FormZ configuration object, and this form configuration object.
     *
     * @return Result
     */
    public function getConfigurationValidationResult()
    {
        if (null === $this->configurationValidationResult
            || $this->lastConfigurationHash !== $this->formObject->getHash()
        ) {
            $configurationObject = $this->getConfigurationObject();
            $this->configurationValidationResult = $this->refreshConfigurationValidationResult($configurationObject);
        }

        return $this->configurationValidationResult;
    }

    /**
     * Resets the validation result and merges it with the global FormZ
     * configuration.
     *
     * @param ConfigurationObjectInstance $configurationObject
     * @return Result
     */
    protected function refreshConfigurationValidationResult(ConfigurationObjectInstance $configurationObject)
    {
        $result = new Result;
        $formzConfigurationValidationResult = $this->configurationFactory
            ->getFormzConfiguration()
            ->getValidationResult();

        $result->merge($formzConfigurationValidationResult);

        $result->forProperty('forms.' . $this->formObject->getClassName())
            ->merge($configurationObject->getValidationResult());

        return $result;
    }

    /**
     * @return ConfigurationObjectInstance
     */
    protected function getConfigurationObjectFromCache()
    {
        $cacheInstance = $this->getCacheInstance();
        $cacheIdentifier = 'configuration-' . $this->formObject->getHash();

        if ($cacheInstance->has($cacheIdentifier)) {
            $configurationObject = $cacheInstance->get($cacheIdentifier);
        } else {
            $configurationObject = $this->buildConfigurationObject();

            if (false === $configurationObject->getValidationResult()->hasErrors()) {
                $cacheInstance->set($cacheIdentifier, $configurationObject);
            }
        }

        return $configurationObject;
    }

    /**
     * @return ConfigurationObjectInstance
     */
    protected function buildConfigurationObject()
    {
        return $this->getConfigurationObjectInstance($this->sanitizeConfiguration($this->configurationArray));
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

        foreach ($this->formObject->getProperties() as $property) {
            $sanitizedFieldsConfiguration[$property] = (isset($fieldsConfiguration[$property]))
                ? $fieldsConfiguration[$property]
                : [];
        }

        $configuration['fields'] = $sanitizedFieldsConfiguration;

        return $configuration;
    }

    /**
     * @param array $configuration
     * @return ConfigurationObjectInstance
     */
    protected function getConfigurationObjectInstance(array $configuration)
    {
        return ConfigurationObjectFactory::getInstance()->get(Form::class, $configuration);
    }

    /**
     * @return FrontendInterface
     */
    protected function getCacheInstance()
    {
        return CacheService::get()->getCacheInstance();
    }

    /**
     * @param ConfigurationFactory $configurationFactory
     */
    public function injectConfigurationFactory(ConfigurationFactory $configurationFactory)
    {
        $this->configurationFactory = $configurationFactory;
    }

    public function __sleep()
    {
        return ['formObject', 'configurationArray'];
    }

    /**
     * When this class is unserialized, the dependencies are injected.
     */
    public function __wakeup()
    {
        /** @var ConfigurationFactory $configurationFactory */
        $configurationFactory = Core::instantiate(ConfigurationFactory::class);

        $this->injectConfigurationFactory($configurationFactory);
    }
}
