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

namespace Romm\Formz\Core;

use Romm\Formz\Service\TypoScriptService;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Service\EnvironmentService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class containing general functions.
 */
class Core implements SingletonInterface
{
    const EXTENSION_KEY = 'formz';

    /**
     * @var Core
     */
    protected static $instance;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var TypoScriptService
     */
    protected $typoScriptService;

    /**
     * @var EnvironmentService
     */
    protected $environmentService;

    /**
     * Contains the actual language key.
     *
     * @var string
     */
    private $languageKey;

    /**
     * @var array
     */
    private $extensionConfiguration;

    /**
     * @return Core
     */
    public static function get()
    {
        if (null === self::$instance) {
            /** @var ObjectManager $objectManager */
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

            self::$instance = $objectManager->get(self::class);
        }

        return self::$instance;
    }

    /**
     * Translation handler. Does the same job as Extbase translation tools,
     * expect that if the index to the LLL reference is not found, the index is
     * returned (Extbase would have returned an empty string).
     *
     * @param    string $index        The index to the LLL reference.
     * @param    string $extensionKey Key of the extension containing the LLL reference.
     * @param    array  $arguments    Arguments passed over to vsprintf.
     * @return   string               The translated string.
     */
    public function translate($index, $extensionKey = null, $arguments = null)
    {
        $extensionKey = ($extensionKey) ?: self::EXTENSION_KEY;
        $result = LocalizationUtility::translate($index, $extensionKey, $arguments);
        if ($result === '' && $index !== '') {
            $result = $index;
        }

        return $result;
    }

    /**
     * Converts an array to a clean JSON string which can be used by JavaScript.
     *
     * @param array $array
     * @return string
     */
    public function arrayToJavaScriptJson(array $array)
    {
        return json_encode($array, JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_TAG);
    }

    /**
     * Return the wanted extension configuration.
     *
     * @param string $configurationName
     * @return mixed
     */
    public function getExtensionConfiguration($configurationName)
    {
        $result = null;
        $extensionConfiguration = $this->getFullExtensionConfiguration();

        if (null === $configurationName) {
            $result = $extensionConfiguration;
        } elseif (ArrayUtility::isValidPath($extensionConfiguration, $configurationName, '.')) {
            $result = ArrayUtility::getValueByPath($extensionConfiguration, $configurationName, '.');
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getFullExtensionConfiguration()
    {
        if (null === $this->extensionConfiguration) {
            $this->extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][self::EXTENSION_KEY]);

            if (false === $this->extensionConfiguration) {
                $this->extensionConfiguration = [];
            }
        }

        return $this->extensionConfiguration;
    }

    /**
     * Returns the current language key.
     *
     * @return string
     */
    public function getLanguageKey()
    {
        if (null === $this->languageKey) {
            $this->languageKey = 'default';

            if ($this->environmentService->isEnvironmentInFrontendMode()) {
                $pageController = $this->getPageController();

                if (isset($pageController->config['config']['language'])) {
                    $this->languageKey = $pageController->config['config']['language'];
                }
            } else {
                $backendUser = $this->getBackendUser();

                if (strlen($backendUser->uc['lang']) > 0) {
                    $this->languageKey = $backendUser->uc['lang'];
                }
            }
        }

        return $this->languageKey;
    }

    /**
     * Will check if the TypoScript was indeed included, as it contains required
     * configuration to make the forms work properly.
     *
     * @return bool
     */
    public function isTypoScriptIncluded()
    {
        return null !== $this->typoScriptService->getExtensionConfigurationFromPath('settings.typoScriptIncluded');
    }

    /**
     * @return bool
     */
    public function isInDebugMode()
    {
        return (bool)$this->getExtensionConfiguration('debugMode');
    }

    /**
     * @param string|null $path If a string is given, it will be precessed by the extension relative path and returned.
     * @return string
     */
    public function getExtensionRelativePath($path = null)
    {
        $relativePath = ExtensionManagementUtility::siteRelPath('formz');

        if ($this->environmentService->isEnvironmentInBackendMode()) {
            $relativePath = '../' . $relativePath;
        }

        return (null !== $path)
            ? $relativePath . $path
            : $relativePath;
    }

    /**
     * @param string $path
     * @return string
     */
    public function getResourceRelativePath($path)
    {
        $relativePath = rtrim(
            PathUtility::getRelativePath(
                GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT'),
                GeneralUtility::getFileAbsFileName($path)
            ),
            '/'
        );

        if ($this->environmentService->isEnvironmentInBackendMode()) {
            $relativePath = '../' . $relativePath;
        }

        return $relativePath;
    }

    /**
     * Sanitizes a string: lower case with dash separation.
     *
     * @param string $string
     * @return string
     */
    public function sanitizeString($string)
    {
        $string = str_replace('_', '-', GeneralUtility::camelCaseToLowerCaseUnderscored($string));

        while (strpos($string, '--')) {
            $string = str_replace('--', '-', $string);
        }

        return $string;
    }

    /**
     * Returns a unique hash for the context of the current request, depending
     * on whether the request comes from frontend or backend.
     *
     * @return string
     */
    public function getContextHash()
    {
        return ($this->environmentService->isEnvironmentInFrontendMode())
            ? 'fe-' . $this->getPageController()->id
            : 'be-' . $this->sanitizeString(GeneralUtility::_GET('M'));
    }

    /**
     * Shortcut for object manager `get()` function.
     *
     * @param string $className
     * @return object
     */
    public static function instantiate($className)
    {
        $objectManager = self::get()->getObjectManager();

        return call_user_func_array([$objectManager, 'get'], func_get_args());
    }

    /**
     * @return ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param TypoScriptService $typoScriptService
     */
    public function injectTypoScriptService(TypoScriptService $typoScriptService)
    {
        $this->typoScriptService = $typoScriptService;
    }

    /**
     * @param EnvironmentService $environmentService
     */
    public function injectEnvironmentService(EnvironmentService $environmentService)
    {
        $this->environmentService = $environmentService;
    }

    /**
     * Returns the extension key.
     *
     * @return string
     */
    public function getExtensionKey()
    {
        return self::EXTENSION_KEY;
    }

    /**
     * @return TypoScriptFrontendController
     */
    public function getPageController()
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * @return BackendUserAuthentication
     */
    public function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }
}
