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

namespace Romm\Formz\Utility;

use Romm\Formz\Core\Core;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Service\EnvironmentService;
use TYPO3\CMS\Extbase\Service\TypoScriptService;

/**
 * Handles the TypoScript configuration of the extension.
 */
class TypoScriptUtility implements SingletonInterface
{
    const EXTENSION_CONFIGURATION_PATH = 'config.tx_formz';

    const PAGES_CONFIGURATION_HASHES_CACHE_IDENTIFIER = 'ts-conf-hash-pages';

    /**
     * @var EnvironmentService
     */
    protected $environmentService;

    /**
     * @var TypoScriptService
     */
    protected $typoScriptService;

    /**
     * Storage for the pages configuration.
     *
     * @var array
     */
    protected $pageConfiguration = [];

    /**
     * @var array
     */
    protected $pagesConfigurationHashes;

    /**
     * Calls the function `getConfigurationFromPath`, but uses the extension
     * configuration path as root path.
     *
     * @param string        $path      The path to the configuration value. If null is given, the whole extension configuration is returned.
     * @param int|null|bool $pageUid   The uid of the page you want the TypoScript configuration from. If `null` is given, the current page uid is used.
     * @param string        $delimiter The delimiter for the path. Default is ".".
     * @return mixed|null
     */
    public function getExtensionConfigurationFromPath($path = null, $pageUid = null, $delimiter = '.')
    {
        $extensionConfiguration = $this->getFullExtensionConfiguration($pageUid);

        if (null === $path) {
            $result = $extensionConfiguration;
        } else {
            $result = (ArrayUtility::isValidPath($extensionConfiguration, $path, $delimiter))
                ? ArrayUtility::getValueByPath($extensionConfiguration, $path, $delimiter)
                : null;
        }

        return $result;
    }

    /**
     * Returns the TypoScript configuration for the given form class name.
     *
     * @param string $formClassName
     * @return array
     */
    public function getFormConfiguration($formClassName)
    {
        $formzConfiguration = $this->getExtensionConfigurationFromPath();

        return (isset($formzConfiguration['forms'][$formClassName]))
            ? $formzConfiguration['forms'][$formClassName]
            : [];
    }

    /**
     * Returns the full Formz TypoScript configuration, but without the `forms`
     * key.
     *
     * @return array
     */
    public function getFormzConfiguration()
    {
        $configuration = $this->getExtensionConfigurationFromPath();
        unset($configuration['forms']);

        return $configuration;
    }

    /**
     * This function will fetch the extension TypoScript configuration. There
     * are two levels of cache: one cache entry is used to store identifiers for
     * the second level of configuration caches: for every page id on which
     * there is a need to access the Formz configuration.
     *
     * @param int|null $pageUid The uid of the page you want the TypoScript configuration from. If `null` is given, the current page uid is used.
     * @return array
     */
    protected function getFullExtensionConfiguration($pageUid = null)
    {
        $result = null;
        $pageUid = $this->getRealPageUid($pageUid);
        $cacheInstance = Core::get()->getCacheInstance();

        if (null === $this->pagesConfigurationHashes) {
            $this->pagesConfigurationHashes = ($cacheInstance->has(self::PAGES_CONFIGURATION_HASHES_CACHE_IDENTIFIER))
                ? $cacheInstance->get(self::PAGES_CONFIGURATION_HASHES_CACHE_IDENTIFIER)
                : [];
        }

        if (true === isset($this->pagesConfigurationHashes[$pageUid])) {
            $hash = $this->pagesConfigurationHashes[$pageUid];

            if ($cacheInstance->has($hash)) {
                $result = $cacheInstance->get($hash);
            }
        }

        if (null === $result) {
            $result = $this->getConfiguration($pageUid);

            $result = (ArrayUtility::isValidPath($result, self::EXTENSION_CONFIGURATION_PATH, '.'))
                ? ArrayUtility::getValueByPath($result, self::EXTENSION_CONFIGURATION_PATH, '.')
                : [];

            $hash = 'ts-conf-page-' . sha1(serialize($result));

            $cacheInstance->set($hash, $result);

            $this->pagesConfigurationHashes[$pageUid] = $hash;
            $cacheInstance->set(self::PAGES_CONFIGURATION_HASHES_CACHE_IDENTIFIER, $this->pagesConfigurationHashes);
        }

        return $result;
    }

    /**
     * Returns the TypoScript configuration, including the static configuration
     * from files (see function `getExtensionConfiguration()`).
     *
     * As this function does not save the configuration in cache, we advise not
     * to call it, and prefer using the function `getConfigurationFromPath()`
     * instead, which has its own caching system.
     *
     * It can still be useful to get the whole TypoScript configuration, so the
     * function remains public, but use with caution!
     *
     * @param int|null $pageUid The uid of the page you want the TypoScript configuration from. If `null` is given, the current page uid is used.
     * @return array The configuration.
     */
    public function getConfiguration($pageUid = null)
    {
        $pageUid = $this->getRealPageUid($pageUid);

        if (!array_key_exists($pageUid, $this->pageConfiguration)) {
            if ($this->environmentService->isEnvironmentInFrontendMode()) {
                $typoScriptArray = Core::get()->getPageController()->tmpl->setup;
            } else {
                // @todo: backend context
                $typoScriptArray = [];
            }

            $this->pageConfiguration[$pageUid] = $this->typoScriptService->convertTypoScriptArrayToPlainArray($typoScriptArray);
        }

        return $this->pageConfiguration[$pageUid];
    }

    /**
     * Determines the real page uid, depending on the type of the parameter.
     *
     * @param  int|null $pageUid The page uid.
     * @return int|null The real page uid.
     */
    private function getRealPageUid($pageUid)
    {
        return ($pageUid === null)
            ? Core::get()->getCurrentPageUid()
            : $pageUid;
    }

    /**
     * @param EnvironmentService $environmentService
     */
    public function injectEnvironmentService(EnvironmentService $environmentService)
    {
        $this->environmentService = $environmentService;
    }

    /**
     * @param TypoScriptService $typoScriptService
     */
    public function injectTypoScriptService(TypoScriptService $typoScriptService)
    {
        $this->typoScriptService = $typoScriptService;
    }
}
