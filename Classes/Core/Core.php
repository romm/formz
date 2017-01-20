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

use Romm\Formz\Service\ExtensionService;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class containing general functions.
 */
class Core implements SingletonInterface
{
    /**
     * @var Core
     */
    protected static $instance;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

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
        $extensionKey = ($extensionKey) ?: ExtensionService::get()->getExtensionKey();
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
