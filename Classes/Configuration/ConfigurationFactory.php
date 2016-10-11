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

namespace Romm\Formz\Configuration;

use Romm\ConfigurationObject\ConfigurationObjectInstance;
use Romm\ConfigurationObject\ConfigurationObjectFactory;
use Romm\Formz\Configuration\Form\Form;
use Romm\Formz\Core\Core;
use Romm\Formz\Form\FormObject;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Error\Result;

/**
 * This class is used to build and manage the whole Formz configuration: from a
 * plain configuration array, it builds an entire tree object which will give
 * all the features from the `configuration_object` extension (parent
 * inheritance, array keys save, etc.).
 */
class ConfigurationFactory implements SingletonInterface
{

    /**
     * @var ConfigurationObjectInstance[]
     */
    protected $instances = [];

    /**
     * @var array
     */
    protected $cacheIdentifiers = [];

    /**
     * Returns the global Formz configuration.
     *
     * Two cache layers are used:
     *
     * - A local cache which will avoid fetching the configuration every time
     *   the current script needs it.
     * - A system cache, which will store the configuration instance when it has
     *   been built, improving performance for next scripts.
     *
     * @return ConfigurationObjectInstance
     */
    public function getFormzConfiguration()
    {
        $cacheIdentifier = $this->getCacheIdentifier();

        if (null === $this->instances[$cacheIdentifier]) {
            $this->instances[$cacheIdentifier] = $this->getFormzConfigurationFromCache($cacheIdentifier);
        }

        return $this->instances[$cacheIdentifier];
    }

    /**
     * Will fetch the configuration from cache, and build it if not found. It
     * wont be stored in cache if any error is found. This is done this way to
     * avoid the integrator to be forced to flush caches when errors are found.
     *
     * @param string $cacheIdentifier
     * @return ConfigurationObjectInstance
     */
    protected function getFormzConfigurationFromCache($cacheIdentifier)
    {
        $cacheInstance = Core::get()->getCacheInstance();

        if ($cacheInstance->has($cacheIdentifier)) {
            $instance = $cacheInstance->get($cacheIdentifier);
        } else {
            $instance = $this->buildFormzConfiguration();

            if (false === $instance->getValidationResult()->hasErrors()) {
                $cacheInstance->set($cacheIdentifier, $instance);
            }
        }

        return $instance;
    }

    /**
     * @see getFormzConfiguration()
     *
     * @return ConfigurationObjectInstance
     */
    protected function buildFormzConfiguration()
    {
        $configuration = Core::get()->getTypoScriptUtility()->getFormzConfiguration();
        $instance = ConfigurationObjectFactory::getInstance()
            ->get(Configuration::class, $configuration);

        /** @var Configuration $instanceObject */
        $instanceObject = $instance->getObject(true);
        $instanceObject->calculateHashes();

        return $instance;
    }

    /**
     * Will fetch the TypoScript configuration for the given form class name,
     * convert it to a configuration object, then add it to the list of forms in
     * the global Formz configuration - which you can access with the function
     * `getFormzConfiguration()`.
     *
     * @param string $className
     * @param string $name
     * @return $this
     * @throws \Exception
     */
    public function addFormFromClassName($className, $name)
    {
        /** @var Configuration $formzConfigurationObject */
        $formzConfigurationObject = $this->getFormzConfiguration()->getObject(true);

        if (false === $formzConfigurationObject->hasForm($className, $name)) {
            $formObject = Core::get()->getFormObjectFactory()->getInstanceFromClassName($className, $name);
            $formObjectConfiguration = $formObject->getConfigurationObject();
            /** @var Form $formConfiguration */
            $formConfiguration = $formObjectConfiguration->getObject(true);

            $formzConfigurationObject->addForm($className, $name, $formConfiguration);
        }

        return $this;
    }

    /**
     * This function will merge the validation results of both the global Formz
     * configuration object, and the given form configuration object.
     *
     * @param FormObject $formObject
     * @return Result
     */
    public function mergeValidationResultWithFormObject(FormObject $formObject)
    {
        $result = new Result();
        $result->merge($this->getFormzConfiguration()->getValidationResult());

        $result->forProperty('forms.' . $formObject->getClassName())
            ->merge($formObject->getConfigurationObject()->getValidationResult());

        return $result;
    }

    /**
     * @return string
     */
    protected function getCacheIdentifier()
    {
        $pageUid = Core::get()->getCurrentPageUid();

        if (false === array_key_exists($pageUid, $this->cacheIdentifiers)) {
            $configuration = Core::get()->getTypoScriptUtility()->getFormzConfiguration();

            $this->cacheIdentifiers[$pageUid] = 'formz-configuration-' . sha1(serialize($configuration));
        }

        return $this->cacheIdentifiers[$pageUid];
    }
}
