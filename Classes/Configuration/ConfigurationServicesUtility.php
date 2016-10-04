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

use Romm\ConfigurationObject\Service\Items\Cache\CacheService;
use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\Service\ServiceInterface;
use Romm\Formz\Core\Core;
use TYPO3\CMS\Core\Cache\Backend\AbstractBackend;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigurationServicesUtility implements SingletonInterface
{

    /**
     * @var ConfigurationServicesUtility
     */
    protected static $instance;

    /**
     * @var string
     */
    protected $backendCache;

    /**
     * Returns an instance of this class.
     *
     * @return ConfigurationServicesUtility
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = GeneralUtility::makeInstance(self::class);
        }

        return self::$instance;
    }

    /**
     * Will add the cache service configured with the TypoScript backend cache
     * type parameter.
     *
     * @param ServiceFactory $serviceFactory
     */
    public function addCacheServiceToServiceFactory(ServiceFactory $serviceFactory)
    {
        $serviceFactory->attach(ServiceInterface::SERVICE_CACHE)
            ->with(ServiceInterface::SERVICE_CACHE)
            ->setOption(CacheService::OPTION_CACHE_BACKEND, $this->getBackendCache());
    }

    /**
     * Returns the backend cache type configured in TypoScript at the path
     * `settings.defaultBackendCache`.
     *
     * @return string
     * @throws \Exception
     */
    protected function getBackendCache()
    {
        if (null === $this->backendCache) {
            $backendCache = Core::getTypoScriptUtility()
                ->getExtensionConfigurationFromPath('settings.defaultBackendCache');

            if (false === class_exists($backendCache)
                && false === in_array(AbstractBackend::class, class_parents($backendCache))
            ) {
                throw new \Exception(
                    'The cache class name given in configuration "config.tx_formz.settings.defaultBackendCache" must inherit "' . AbstractBackend::class . '" (current value: "' . (string)$backendCache . '")',
                    1459251263
                );
            }
        }

        return $this->backendCache;
    }
}
