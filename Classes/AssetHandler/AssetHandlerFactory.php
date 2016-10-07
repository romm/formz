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

use Romm\Formz\Form\FormObject;
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
     * Contains the instances of this class for every form.
     *
     * @var array
     */
    protected static $instances = [];

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
     * @param FormObject        $formObject Configuration of the form.
     * @param ControllerContext $controllerContext
     * @return AssetHandlerFactory
     */
    public static function get(FormObject $formObject, ControllerContext $controllerContext)
    {
        if (false === isset(self::$instances[$formObject->getClassName()])) {
            self::$instances[$formObject->getClassName()] = new AssetHandlerFactory($formObject, $controllerContext);
        }

        return self::$instances[$formObject->getClassName()];
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
