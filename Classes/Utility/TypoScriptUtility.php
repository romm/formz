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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Service\TypoScriptService;

/**
 * Handles the TypoScript configuration of the extension.
 */
class TypoScriptUtility implements SingletonInterface
{
    const EXTENSION_CONFIGURATION_PATH = 'config.tx_formz';

    /**
     * @var TypoScriptService
     */
    protected $typoScriptService;

    /**
     * Storage for the pages configuration.
     *
     * @var array
     */
    protected $configuration = [];

    /**
     * Returns the TypoScript configuration at the given path (starting from
     * Formz configuration root).
     *
     * @param string $path
     * @return mixed
     */
    public function getExtensionConfigurationFromPath($path)
    {
        $extensionConfiguration = $this->getExtensionConfiguration();

        return (ArrayUtility::isValidPath($extensionConfiguration, $path, '.'))
            ? ArrayUtility::getValueByPath($extensionConfiguration, $path, '.')
            : null;
    }

    /**
     * Returns the TypoScript configuration for the given form class name.
     *
     * @param string $formClassName
     * @return array
     */
    public function getFormConfiguration($formClassName)
    {
        $formzConfiguration = $this->getExtensionConfiguration();

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
        $configuration = $this->getExtensionConfiguration();
        unset($configuration['forms']);

        return $configuration;
    }

    /**
     * This function will fetch the extension TypoScript configuration, and
     * store it in cache for further usage.
     *
     * The configuration array is not stored in cache if the configuration
     * property `settings.typoScriptIncluded` is not found.
     *
     * @return array
     */
    protected function getExtensionConfiguration()
    {
        $cacheInstance = Core::get()->getCacheInstance();
        $hash = $this->getContextHash();

        if ($cacheInstance->has($hash)) {
            $result = $cacheInstance->get($hash);
        } else {
            $result = $this->getFullConfiguration();
            $result = (ArrayUtility::isValidPath($result, self::EXTENSION_CONFIGURATION_PATH, '.'))
                ? ArrayUtility::getValueByPath($result, self::EXTENSION_CONFIGURATION_PATH, '.')
                : [];

            if (ArrayUtility::isValidPath($result, 'settings.typoScriptIncluded', '.')) {
                $cacheInstance->set($hash, $result);
            }
        }

        return $result;
    }

    /**
     * Returns the full TypoScript configuration, based on the context of the
     * current request.
     *
     * @return array
     */
    protected function getFullConfiguration()
    {
        $contextHash = $this->getContextHash();

        if (false === array_key_exists($contextHash, $this->configuration)) {
            if (Core::get()->getEnvironmentService()->isEnvironmentInFrontendMode()) {
                $typoScriptArray = $this->getFrontendTypoScriptConfiguration();
            } else {
                $typoScriptArray = $this->getBackendTypoScriptConfiguration();
            }

            $this->configuration[$contextHash] = $this->typoScriptService->convertTypoScriptArrayToPlainArray($typoScriptArray);
        }

        return $this->configuration[$contextHash];
    }

    /**
     * @return array
     */
    protected function getFrontendTypoScriptConfiguration()
    {
        return Core::get()->getPageController()->tmpl->setup;
    }

    /**
     * @return array
     */
    protected function getBackendTypoScriptConfiguration()
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = Core::get()->getObjectManager()->get(ConfigurationManager::class);

        return $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
    }

    /**
     * Returns a unique hash for the context of the current request, depending
     * on wether the request comes from frontend or backend.
     *
     * @return string
     */
    protected function getContextHash()
    {
        $hash = (Core::get()->getEnvironmentService()->isEnvironmentInFrontendMode())
            ? 'fe-' . Core::get()->getCurrentPageUid()
            : 'be-' . Core::get()->sanitizeString(GeneralUtility::_GET('M'));

        return 'ts-conf-' . $hash;
    }

    /**
     * @param TypoScriptService $typoScriptService
     */
    public function injectTypoScriptService(TypoScriptService $typoScriptService)
    {
        $this->typoScriptService = $typoScriptService;
    }
}
