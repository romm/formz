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

use Romm\ConfigurationObject\ConfigurationObjectInterface;
use Romm\ConfigurationObject\Service\Items\Cache\CacheService;
use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\Service\ServiceInterface;
use Romm\ConfigurationObject\Traits\ConfigurationObject\ArrayConversionTrait;
use Romm\ConfigurationObject\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;
use Romm\Formz\Configuration\Settings\Settings;
use Romm\Formz\Configuration\View\View;
use Romm\Formz\Service\CacheService as InternalCacheService;
use Romm\Formz\Service\HashService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Configuration extends AbstractConfiguration implements ConfigurationObjectInterface
{
    use DefaultConfigurationObjectTrait;
    use ArrayConversionTrait;

    /**
     * @var \Romm\Formz\Configuration\Settings\Settings
     */
    protected $settings;

    /**
     * @var \Romm\Formz\Configuration\View\View
     */
    protected $view;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var ConfigurationState
     */
    private $state;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->settings = GeneralUtility::makeInstance(Settings::class);
        $this->settings->attachParent($this);

        $this->view = GeneralUtility::makeInstance(View::class);
        $this->view->attachParent($this);

        $this->state = GeneralUtility::makeInstance(ConfigurationState::class);
    }

    /**
     * @return Settings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Calculates a hash for this configuration, which can be used as a unique
     * identifier. It should be called once, before the configuration is put in
     * cache, so it is not needed to call it again after being fetched from
     * cache.
     */
    public function calculateHash()
    {
        return $this->hash = HashService::get()->getHash(serialize($this));
    }

    /**
     * @return string
     */
    public function getHash()
    {
        if (null === $this->hash) {
            $this->hash = $this->calculateHash();
        }

        return $this->hash;
    }

    /**
     * @return ConfigurationState
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Will initialize correctly the configuration object settings.
     *
     * @return ServiceFactory
     */
    public static function getConfigurationObjectServices()
    {
        return ServiceFactory::getInstance()
            ->attach(ServiceInterface::SERVICE_CACHE)
            ->with(ServiceInterface::SERVICE_CACHE)
            ->setOption(CacheService::OPTION_CACHE_NAME, InternalCacheService::CONFIGURATION_OBJECT_CACHE_IDENTIFIER)
            ->setOption(CacheService::OPTION_CACHE_BACKEND, InternalCacheService::get()->getBackendCache())
            ->attach(ServiceInterface::SERVICE_PARENTS)
            ->attach(ServiceInterface::SERVICE_DATA_PRE_PROCESSOR)
            ->attach(ServiceInterface::SERVICE_MIXED_TYPES);
    }
}
