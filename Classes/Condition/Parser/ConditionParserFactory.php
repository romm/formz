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

namespace Romm\Formz\Condition\Parser;

use Romm\Formz\Form\Definition\Field\Activation\ActivationInterface;
use Romm\Formz\Service\CacheService;
use Romm\Formz\Service\HashService;
use Romm\Formz\Service\Traits\SelfInstantiateTrait;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Factory class allowing to parse a condition to get an instance of
 * `ConditionTree`.
 */
class ConditionParserFactory implements SingletonInterface
{
    use SelfInstantiateTrait;

    /**
     * @var ConditionTree[]
     */
    private $trees = [];

    /**
     * Will parse a condition expression, to build a tree containing one or
     * several nodes which represent the condition. See the class
     * `ConditionTree` for more information.
     *
     * @param ActivationInterface $activation
     * @return ConditionTree
     */
    public function parse(ActivationInterface $activation)
    {
        $hash = 'condition-tree-' .
            HashService::get()->getHash(serialize([
                $activation->getExpression(),
                $activation->getAllConditions()
            ]));

        if (false === array_key_exists($hash, $this->trees)) {
            $this->trees[$hash] = $this->getConditionTree($hash, $activation);
        }

        return $this->trees[$hash];
    }

    /**
     * @param string              $cacheIdentifier
     * @param ActivationInterface $activation
     * @return ConditionTree
     */
    protected function getConditionTree($cacheIdentifier, ActivationInterface $activation)
    {
        $cacheInstance = CacheService::get()->getCacheInstance();

        /** @var ConditionTree $instance */
        if ($cacheInstance->has($cacheIdentifier)) {
            $instance = $cacheInstance->get($cacheIdentifier);
        } else {
            $instance = $this->buildConditionTree($activation);
            $cacheInstance->set($cacheIdentifier, $instance);
        }

        return $instance;
    }

    /**
     * @param ActivationInterface $activation
     * @return ConditionTree
     */
    protected function buildConditionTree(ActivationInterface $activation)
    {
        return ConditionParser::get()->parse($activation);
    }
}
