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

namespace Romm\Formz\AssetHandler;

use Romm\Formz\Configuration\Form\Form;
use Romm\Formz\Form\FormObject;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Abstract class which must be inherited by an asset handler.
 *
 * An asset handler is a helper for getting useful information for a given
 * language.
 */
abstract class AbstractAssetHandler implements SingletonInterface
{

    /**
     * @var AssetHandlerFactory
     */
    protected $assetHandlerFactory;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var AbstractAssetHandler[]
     */
    protected static $instances = [];

    /**
     * @param AssetHandlerFactory $assetHandlerFactory
     * @return $this
     */
    public static function with(AssetHandlerFactory $assetHandlerFactory)
    {
        $hash = spl_object_hash($assetHandlerFactory);

        if (false === isset(self::$instances[$hash])) {
            self::$instances[$hash] = [];
        }

        if (false === isset(self::$instances[$hash][get_called_class()])) {
            /** @var ObjectManager $objectManager */
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

            $instance = $objectManager->get(get_called_class());
            $instance->setAssetHandlerFactory($assetHandlerFactory);
            self::$instances[$hash][get_called_class()] = $instance;
        }

        return self::$instances[$hash][get_called_class()];
    }

    /**
     * @return FormObject
     */
    public function getFormObject()
    {
        return $this->assetHandlerFactory->getFormObject();
    }

    /**
     * @return Form
     */
    public function getFormConfiguration()
    {
        return $this->getFormObject()->getConfiguration();
    }

    /**
     * @param AssetHandlerFactory $assetHandlerFactory
     */
    public function setAssetHandlerFactory(AssetHandlerFactory $assetHandlerFactory)
    {
        $this->assetHandlerFactory = $assetHandlerFactory;
    }

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }
}
