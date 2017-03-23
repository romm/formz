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

namespace Romm\Formz\Service;

use Romm\Formz\Service\Traits\SelfInstantiateTrait;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class ExtensionService implements SingletonInterface
{
    use SelfInstantiateTrait;

    const EXTENSION_KEY = 'formz';

    /**
     * @var array
     */
    private $extensionConfiguration;

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
     * @return string
     */
    public function getExtensionRelativePath()
    {
        return ExtensionManagementUtility::siteRelPath(self::EXTENSION_KEY);
    }

    /**
     * @return bool
     */
    public function isInDebugMode()
    {
        return (bool)$this->getExtensionConfiguration('debugMode');
    }

    /**
     * @return string
     */
    public function getExtensionKey()
    {
        return self::EXTENSION_KEY;
    }
}
