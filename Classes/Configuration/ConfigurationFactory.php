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

namespace Romm\Formz\Configuration;

use Romm\ConfigurationObject\ConfigurationObjectFactory;
use Romm\ConfigurationObject\ConfigurationObjectInstance;
use Romm\Formz\Service\CacheService;
use Romm\Formz\Service\ContextService;
use Romm\Formz\Service\HashService;
use Romm\Formz\Service\Traits\ExtendedSelfInstantiateTrait;
use Romm\Formz\Service\TypoScriptService;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * This class is used to build and manage the whole FormZ configuration: from a
 * plain configuration array, it builds an entire tree object which will give
 * all the features from the `configuration_object` extension (parent
 * inheritance, array keys save, etc.).
 */
class ConfigurationFactory implements SingletonInterface
{
    use ExtendedSelfInstantiateTrait;

    /**
     * @var TypoScriptService
     */
    protected $typoScriptService;

    /**
     * @var ConfigurationObjectInstance[]
     */
    protected $instances = [];

    /**
     * @var array
     */
    protected $cacheIdentifiers = [];

    /**
     * Returns the global FormZ configuration.
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

        if (false === array_key_exists($cacheIdentifier, $this->instances)) {
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
        $cacheInstance = CacheService::get()->getCacheInstance();

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
        $configuration = $this->typoScriptService->getFormzConfiguration();
        $instance = ConfigurationObjectFactory::getInstance()
            ->get(Configuration::class, $configuration);

        /** @var Configuration $instanceObject */
        $instanceObject = $instance->getObject(true);
        $instanceObject->calculateHash();

        return $instance;
    }

    /**
     * @return string
     */
    protected function getCacheIdentifier()
    {
        $contextHash = ContextService::get()->getContextHash();

        if (false === array_key_exists($contextHash, $this->cacheIdentifiers)) {
            $configuration = $this->typoScriptService->getFormzConfiguration();

            $this->cacheIdentifiers[$contextHash] = 'formz-configuration-' . HashService::get()->getHash(serialize($configuration));
        }

        return $this->cacheIdentifiers[$contextHash];
    }

    /**
     * @param TypoScriptService $typoScriptService
     */
    public function injectTypoScriptService(TypoScriptService $typoScriptService)
    {
        $this->typoScriptService = $typoScriptService;
    }
}
