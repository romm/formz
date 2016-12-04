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

use Romm\Formz\Condition\Processor\ConditionProcessor;
use Romm\Formz\Condition\Processor\ConditionProcessorFactory;
use Romm\Formz\Core\Core;
use Romm\Formz\Form\FormObject;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
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
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var AssetHandlerFactory
     */
    protected $assetHandlerFactory;

    /**
     * @var ConditionProcessor
     */
    protected $conditionProcessor;

    /**
     * Constructor, will set up variables.
     *
     * @param AssetHandlerFactory $assetHandlerFactory
     */
    public function __construct(AssetHandlerFactory $assetHandlerFactory)
    {
        $this->assetHandlerFactory = $assetHandlerFactory;
        $this->objectManager = Core::get()->getObjectManager();
        $this->conditionProcessor = ConditionProcessorFactory::getInstance()
            ->get($assetHandlerFactory->getFormObject());
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
     * Just an alias to get the controller context faster.
     *
     * @return ControllerContext
     */
    public function getControllerContext()
    {
        return $this->assetHandlerFactory->getControllerContext();
    }
}
