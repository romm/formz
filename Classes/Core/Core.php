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

namespace Romm\Formz\Core;

use Romm\Formz\Configuration\ConfigurationFactory;
use Romm\Formz\Form\FormObjectFactory;
use Romm\Formz\Utility\TypoScriptUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\EnvironmentService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class containing general functions.
 */
class Core implements SingletonInterface
{

    const EXTENSION_KEY = 'formz';
    const CACHE_IDENTIFIER = 'cache_formz';
    const GENERATED_FILES_PATH = 'typo3temp/Formz/';

    /**
     * @var int|null
     */
    private static $currentPageUid = -1;

    /**
     * @var ObjectManager
     */
    private static $objectManager;

    /**
     * @var TypoScriptUtility
     */
    private static $typoScriptUtility;

    /**
     * @var ConfigurationFactory
     */
    private static $configurationFactory;

    /**
     * @var FormObjectFactory
     */
    private static $formObjectFactory;

    /**
     * Contains the actual language key.
     *
     * @var string
     */
    private static $languageKey;

    /**
     * @var array
     */
    private static $extensionConfiguration;

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
    public static function translate($index, $extensionKey = null, $arguments = null)
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
    public static function arrayToJavaScriptJson(array $array)
    {
        return json_encode($array, JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_TAG);
    }

    /**
     * Returns the current page uid, in a frontend or backend context.
     *
     * Returns null if the uid can't be found (backend module, ajax call, etc.).
     *
     * @return int|null
     */
    public static function getCurrentPageUid()
    {
        if (-1 === self::$currentPageUid) {
            /** @var EnvironmentService $environmentService */
            $environmentService = GeneralUtility::makeInstance(EnvironmentService::class);

            $id = ($environmentService->isEnvironmentInFrontendMode())
                ? self::getPageController()->id
                : GeneralUtility::_GP('id');

            if (false === MathUtility::canBeInterpretedAsInteger($id)
                || intval($id) < 0
            ) {
                $id = null;
            }

            self::$currentPageUid = $id;
        }

        return self::$currentPageUid;
    }

    /**
     * Allows you to set manually the current page uid. Useful when editing a
     * record, for example.
     *
     * @param int $uid The uid of the page.
     */
    public static function setCurrentPageUid($uid)
    {
        self::$currentPageUid = intval($uid);
    }

    /**
     * Returns the cache instance for this extension.
     *
     * @return FrontendInterface|null
     */
    public static function getCacheInstance()
    {
        /** @var $cacheManager CacheManager */
        $cacheManager = self::getObjectManager()->get(CacheManager::class);
        $result = null;
        if ($cacheManager->hasCache(self::CACHE_IDENTIFIER)) {
            $result = $cacheManager->getCache(self::CACHE_IDENTIFIER);
        }

        return $result;
    }

    /**
     * Generic cache identifier creation for usages in the extension.
     *
     * @param string $string
     * @param string $formClassName
     * @param int    $maxLength
     * @return string
     */
    public static function getCacheIdentifier($string, $formClassName, $maxLength = 55)
    {
        $explodedClassName = explode('\\', $formClassName);

        $identifier = strtolower(
            $string .
            end($explodedClassName) .
            '-' .
            sha1($formClassName)
        );

        return substr($identifier, 0, $maxLength);
    }

    /**
     * Return the extension configuration.
     *
     * @param string $configurationName If null, returns the whole configuration. Otherwise, returns the asked configuration.
     * @return array
     */
    public static function getExtensionConfiguration($configurationName = null)
    {
        if (null === self::$extensionConfiguration) {
            self::$extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][self::EXTENSION_KEY]);
            if (false === self::$extensionConfiguration) {
                self::$extensionConfiguration = [];
            }
        }

        $result = null;
        if (null === $configurationName) {
            $result = self::$extensionConfiguration;
        } elseif (ArrayUtility::isValidPath(self::$extensionConfiguration, $configurationName, '.')) {
            $result = ArrayUtility::getValueByPath(self::$extensionConfiguration, $configurationName, '.');
        }

        return $result;
    }

    /**
     * Function called when clearing TYPO3 caches. It will remove the temporary
     * asset files created by Formz.
     *
     * @param array $parameters
     * @return void
     */
    public function clearCacheCommand($parameters)
    {
        if (false === in_array($parameters['cacheCmd'], ['all', 'system'])) {
            return;
        }

        $files = glob(GeneralUtility::getFileAbsFileName(self::GENERATED_FILES_PATH . '*'));

        if (false === $files) {
            return;
        }

        foreach ($files as $assetCacheFile) {
            unlink($assetCacheFile);
        }
    }

    /**
     * Returns the current language key.
     *
     * @return string
     */
    public static function getLanguageKey()
    {
        if (null === self::$languageKey) {
            self::$languageKey = 'default';

            /** @var EnvironmentService $environmentService */
            $environmentService = GeneralUtility::makeInstance(EnvironmentService::class);

            if ($environmentService->isEnvironmentInFrontendMode()) {
                $pageController = self::getPageController();

                if (isset($pageController->config['config']['language'])) {
                    self::$languageKey = $pageController->config['config']['language'];
                }
            } else {
                $backendUser = self::getBackendUser();

                if (strlen($backendUser->uc['lang']) > 0) {
                    self::$languageKey = $backendUser->uc['lang'];
                }
            }
        }

        return self::$languageKey;
    }

    /**
     * @return bool
     */
    public static function isInDebugMode()
    {
        return (bool)self::getExtensionConfiguration('debugMode');
    }

    /**
     * @return ObjectManager
     */
    public static function getObjectManager()
    {
        if (null === self::$objectManager) {
            self::$objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        }

        return self::$objectManager;
    }

    /**
     * @return TypoScriptUtility
     */
    public static function getTypoScriptUtility()
    {
        if (null === self::$typoScriptUtility) {
            self::$typoScriptUtility = self::getObjectManager()->get(TypoScriptUtility::class);
        }

        return self::$typoScriptUtility;
    }

    /**
     * @return ConfigurationFactory
     */
    public static function getConfigurationFactory()
    {
        if (null === self::$configurationFactory) {
            self::$configurationFactory = self::getObjectManager()->get(ConfigurationFactory::class);
        }

        return self::$configurationFactory;
    }

    /**
     * @return FormObjectFactory
     */
    public static function getFormObjectFactory()
    {
        if (null === self::$formObjectFactory) {
            self::$formObjectFactory = self::getObjectManager()->get(FormObjectFactory::class);
        }

        return self::$formObjectFactory;
    }

    /**
     * Returns the extension key.
     *
     * @return string
     */
    public static function getExtensionKey()
    {
        return self::EXTENSION_KEY;
    }

    /**
     * @return TypoScriptFrontendController
     */
    public static function getPageController()
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * @return BackendUserAuthentication
     */
    public static function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }
}
