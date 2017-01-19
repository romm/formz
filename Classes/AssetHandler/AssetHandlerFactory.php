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

namespace Romm\Formz\AssetHandler;

use Romm\Formz\Exceptions\ClassNotFoundException;
use Romm\Formz\Exceptions\InvalidArgumentTypeException;
use Romm\Formz\Form\FormObject;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;

/**
 * A factory used to get instances of asset handlers. This is useful because it
 * will automatically manage cached instances of asset handlers for a given
 * form.
 *
 * The factory is here to store all information which can be useful to the asset
 * handlers.
 */
class AssetHandlerFactory
{

    /**
     * Contains the instances of factory for every form/controller context.
     *
     * @var array
     */
    protected static $factoryInstances = [];

    /**
     * @var AbstractAssetHandler[]
     */
    protected $instances = [];

    /**
     * @var FormObject
     */
    protected $formObject = [];

    /**
     * @var ControllerContext
     */
    protected $controllerContext;

    /**
     * @param FormObject        $formObject Name of the form class.
     * @param ControllerContext $controllerContext
     * @throws \Exception
     */
    protected function __construct(FormObject $formObject, ControllerContext $controllerContext)
    {
        $this->formObject = $formObject;
        $this->controllerContext = $controllerContext;
    }

    /**
     * Returns a factory instance. The same instance will be returned for the
     * same `$formObject` and `$controllerContext` parameters.
     *
     * @param FormObject        $formObject Configuration of the form.
     * @param ControllerContext $controllerContext
     * @return AssetHandlerFactory
     */
    public static function get(FormObject $formObject, ControllerContext $controllerContext)
    {
        $hash = md5(spl_object_hash($formObject) . spl_object_hash($controllerContext));

        if (false === array_key_exists($hash, self::$factoryInstances)) {
            self::$factoryInstances[$hash] = new self($formObject, $controllerContext);
        }

        return self::$factoryInstances[$hash];
    }

    /**
     * Return an instance of the wanted asset handler. Local storage is handled.
     *
     * @param string $className
     * @return AbstractAssetHandler
     * @throws ClassNotFoundException
     * @throws InvalidArgumentTypeException
     */
    public function getAssetHandler($className)
    {
        if (false === array_key_exists($className, $this->instances)) {
            if (false === class_exists($className)) {
                throw new ClassNotFoundException(
                    'Trying to get an asset handler with a wrong class name: "' . $className . '".',
                    1477468381
                );
            }

            $instance = GeneralUtility::makeInstance($className, $this);

            if (false === $instance instanceof AbstractAssetHandler) {
                throw new InvalidArgumentTypeException(
                    'The asset handler object must be an instance of "' . AbstractAssetHandler::class . '", current type: "' . get_class($instance) . '".',
                    1477468571
                );
            }

            $this->instances[$className] = $instance;
        }

        return $this->instances[$className];
    }

    /**
     * @return FormObject
     */
    public function getFormObject()
    {
        return $this->formObject;
    }

    /**
     * @return ControllerContext
     */
    public function getControllerContext()
    {
        return $this->controllerContext;
    }
}
