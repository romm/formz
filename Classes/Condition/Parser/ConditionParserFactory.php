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

namespace Romm\Formz\Condition\Parser;

use Romm\Formz\Configuration\Form\Condition\Activation\ActivationInterface;
use Romm\Formz\Core\Core;
use Romm\Formz\Service\Traits\FacadeInstanceTrait;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Factory class allowing to parse a condition to get an instance of
 * `ConditionTree`.
 */
class ConditionParserFactory implements SingletonInterface
{
    use FacadeInstanceTrait;

    /**
     * @var ConditionTree[]
     */
    private $trees = [];

    /**
     * Will parse a condition expression, to build a tree containing one or
     * several nodes which represent the condition. See the class
     * `ConditionTree` for more information.
     *
     * @param ActivationInterface $condition The condition instance.
     * @return ConditionTree
     */
    public function parse(ActivationInterface $condition)
    {
        $hash = 'condition-tree-' .
            sha1(serialize([
                $condition->getCondition(),
                $condition->getItems()
            ]));

        if (false === array_key_exists($hash, $this->trees)) {
            $this->trees[$hash] = $this->getConditionTree($hash, $condition);
        }

        return $this->trees[$hash];
    }

    /**
     * @param string              $cacheIdentifier
     * @param ActivationInterface $condition
     * @return ConditionTree
     */
    protected function getConditionTree($cacheIdentifier, ActivationInterface $condition)
    {
        $cacheInstance = Core::get()->getCacheInstance();

        /** @var ConditionTree $instance */
        if ($cacheInstance->has($cacheIdentifier)) {
            $instance = $cacheInstance->get($cacheIdentifier);
        } else {
            $instance = ConditionParser::get()->parse($condition);
            $cacheInstance->set($cacheIdentifier, $instance);
        }

        return $instance;
    }
}
