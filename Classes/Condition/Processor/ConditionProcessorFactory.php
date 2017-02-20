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

namespace Romm\Formz\Condition\Processor;

use Romm\Formz\Core\Core;
use Romm\Formz\Form\FormObject;
use Romm\Formz\Service\CacheService;
use Romm\Formz\Service\Traits\ExtendedFacadeInstanceTrait;
use TYPO3\CMS\Core\SingletonInterface;

class ConditionProcessorFactory implements SingletonInterface
{
    use ExtendedFacadeInstanceTrait {
        get as getInstance;
    }

    /**
     * @var ConditionProcessor[]
     */
    private $processorInstances = [];

    /**
     * @param FormObject $formObject
     * @return ConditionProcessor
     */
    public function get(FormObject $formObject)
    {
        $cacheIdentifier = $this->getCacheIdentifier($formObject);

        if (false === array_key_exists($cacheIdentifier, $this->processorInstances)) {
            $this->processorInstances[$cacheIdentifier] = $this->fetchProcessorInstanceFromCache($cacheIdentifier, $formObject);
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
    protected function fetchProcessorInstanceFromCache($cacheIdentifier, FormObject $formObject)
    {
        $cacheInstance = CacheService::get()->getCacheInstance();

        /** @var ConditionProcessor $instance */
        if ($cacheInstance->has($cacheIdentifier)) {
            $instance = $cacheInstance->get($cacheIdentifier);
            $instance->attachFormObject($formObject);
        } else {
            $instance = $this->getNewProcessorInstance($formObject);
            $instance->calculateAllTrees();

            $cacheInstance->set($cacheIdentifier, $instance);
        }

        return $instance;
    }

    /**
     * Used in unit tests.
     *
     * @param FormObject $formObject
     * @return ConditionProcessor
     */
    protected function getNewProcessorInstance(FormObject $formObject)
    {
        /** @var ConditionProcessor $instance */
        $instance = Core::instantiate(ConditionProcessor::class, $formObject);

        return $instance;
    }

    /**
     * @param FormObject $formObject
     * @return string
     */
    protected function getCacheIdentifier(FormObject $formObject)
    {
        return 'condition-processor-' . $formObject->getHash();
    }
}
