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

use Romm\Formz\Core\Core;
use Romm\Formz\Service\Traits\SelfInstantiateTrait;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class ContentObjectService implements SingletonInterface
{
    use SelfInstantiateTrait;

    /**
     * @param string $table
     * @param int    $uid
     * @param string $extensionName
     * @param string $pluginName
     * @return array
     */
    public function getContentObjectSettings($table, $uid, $extensionName, $pluginName)
    {
        if (empty($table)
            || empty($uid)
            || empty($extensionName)
            || empty($pluginName)
        ) {
            return [];
        }

        /** @var ConfigurationManager $configurationManager */
        $configurationManager = Core::instantiate(ConfigurationManagerInterface::class);
        $contentObjectBackup = $configurationManager->getContentObject();

        $database = Core::get()->getDatabase();

        $content = $database->exec_SELECTgetSingleRow(
            '*',
            $database->quoteStr($table, $table),
            'uid=' . (int)$uid
        );

        /** @var ContentObjectRenderer $contentObject */
        $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $contentObject->start($content, $table);
        $configurationManager->setContentObject($contentObject);

        $configurationManager->setConfiguration([
            'extensionName' => $extensionName,
            'pluginName'    => $pluginName
        ]);

        $configuration = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS);

        $configurationManager->setContentObject($contentObjectBackup);

        return $configuration;
    }
}
