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
use Romm\Formz\Core\Core;
use Romm\Formz\Form\FormObject;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Abstract class which must be inherited by an asset handler.
 *
 * An asset handler is a helper for getting useful information for a given
 * language.
 */
abstract class AbstractAssetHandler
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
     * Constructor, will set up variables.
     *
     * @param AssetHandlerFactory $assetHandlerFactory
     */
    public function __construct(AssetHandlerFactory $assetHandlerFactory)
    {
        $this->assetHandlerFactory = $assetHandlerFactory;
        $this->objectManager = Core::get()->getObjectManager();
    }

    /**
     * Use this function to instantiate a new instance of the class which calls
     * the function. The instance is then directly usable.
     *
     * Example:
     * `MyAssetHandler::with($assetHandlerFactory)->doSomeStuff();`
     *
     * @param AssetHandlerFactory $assetHandlerFactory
     * @return $this
     */
    public static function with(AssetHandlerFactory $assetHandlerFactory)
    {
        $hash = spl_object_hash($assetHandlerFactory);
        $className = get_called_class();

        if (false === isset(self::$instances[$hash])) {
            self::$instances[$hash] = [];
        }

        if (false === isset(self::$instances[$hash][$className])) {
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            self::$instances[$hash][$className] = Core::get()->getObjectManager()
                ->get($className, $assetHandlerFactory);
        }

        return self::$instances[$hash][$className];
    }

    /**
     * Just an alias to get the form object faster.
     *
     * @return FormObject
     */
    public function getFormObject()
    {
        return $this->assetHandlerFactory->getFormObject();
    }

    /**
     * Just an alias to get the form configuration faster.
     *
     * @return Form
     */
    public function getFormConfiguration()
    {
        return $this->getFormObject()->getConfiguration();
    }
}
