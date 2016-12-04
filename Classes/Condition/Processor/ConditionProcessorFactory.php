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

namespace Romm\Formz\Condition\Processor;

use Romm\Formz\Core\Core;
use Romm\Formz\Form\FormObject;
use TYPO3\CMS\Core\SingletonInterface;

class ConditionProcessorFactory implements SingletonInterface
{
    /**
     * @var ConditionProcessorFactory
     */
    private static $instance;

    /**
     * @var ConditionProcessor[]
     */
    private $processorInstances = [];

    /**
     * @return ConditionProcessorFactory
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = Core::get()
                ->getObjectManager()
                ->get(self::class);
        }

        return self::$instance;
    }

    /**
     * @param FormObject $formObject
     * @return ConditionProcessor
     */
    public function get(FormObject $formObject)
    {
        $cacheIdentifier = 'condition-processor-' . $formObject->getHash();

        if (false === array_key_exists($cacheIdentifier, $this->processorInstances)) {
            $this->processorInstances[$cacheIdentifier] = $this->getProcessorInstance($cacheIdentifier, $formObject);
        }

        return $this->processorInstances[$cacheIdentifier];
    }

    /**
     * Will either fetch the processor instance from cache, using the given
     * identifier, or calculate it and store it in cache.
     *
     * @param string     $cacheIdentifier
     * @param FormObject $formObject
     * @return ConditionProcessor
     */
    protected function getProcessorInstance($cacheIdentifier, FormObject $formObject)
    {
        $cacheInstance = Core::get()->getCacheInstance();

        /** @var ConditionProcessor $instance */
        if ($cacheInstance->has($cacheIdentifier)) {
            $instance = $cacheInstance->get($cacheIdentifier);
        } else {
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            $instance = Core::get()
                ->getObjectManager()
                ->get(ConditionProcessor::class, $formObject);

            $instance->calculateAllTrees();
            $instance->attachFormObject($formObject);

            $cacheInstance->set($cacheIdentifier, $instance);
        }

        return $instance;
    }
}
