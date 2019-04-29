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

namespace Romm\Formz\Form\Definition\Step\Step\Substep;

use Romm\Formz\Form\Definition\AbstractFormDefinitionComponent;
use Romm\Formz\Form\Definition\Condition\Activation;
use Romm\Formz\Form\Definition\Condition\ActivationInterface;
use Romm\Formz\Form\Definition\Step\Step\DivergenceStepDefinition;
use Romm\Formz\Form\Definition\Step\Step\Step;

class SubstepDefinition extends AbstractFormDefinitionComponent
{
    /**
     * @var string
     * @validate NotEmpty
     */
    protected $substep;

    /**
     * @var \Romm\Formz\Form\Definition\Condition\Activation
     * @validate Romm.Formz:Internal\ConditionIsValid
     */
    protected $activation;

    /**
     * @var \Romm\Formz\Form\Definition\Step\Step\Substep\SubstepDefinition
     */
    protected $next;

    /**
     * @var \Romm\Formz\Form\Definition\Step\Step\Substep\DivergenceSubstepDefinition[]
     */
    protected $divergence;

    /**
     * @return Substep
     */
    public function getSubstep()
    {
        return $this->withFirstParent(
            Substeps::class,
            function (Substeps $substeps) {
                return $substeps->getEntry($this->substep);
            }
        );
    }

    /**
     * @return Activation
     */
    public function getActivation()
    {
        return $this->activation;
    }

    /**
     * @return bool
     */
    public function hasActivation()
    {
        return $this->activation instanceof ActivationInterface;
    }

    public function getLevel()
    {
        $level = 1;

        $this->alongParents(function ($parent) use (&$level) {
            if ($parent instanceof self) {
                $level++;
            }

            if ($parent instanceof Step) {
                return false;
            }

            return true;
        });

        return $level;
    }

    /**
     * @todo
     *
     * @return bool
     */
    public function isLast()
    {
        return false === $this->hasNextSubstep()
            && false === $this->hasDivergence();
    }

    /**
     * @return bool
     */
    public function hasNextSubstep()
    {
        return null !== $this->next;
    }

    /**
     * @return SubstepDefinition
     */
    public function getNextSubstep()
    {
        if (false === $this->hasNextSubstep()) {
            throw new \Exception('todo'); // @todo
        }

        return $this->next;
    }

    /**
     * @return DivergenceSubstepDefinition[]
     */
    public function getDivergenceSubsteps()
    {
        return $this->divergence;
    }

    /**
     * @return bool
     */
    public function hasDivergence()
    {
        return false === empty($this->divergence);
    }

    /**
     * @return string
     */
    public function hash()
    {
        return serialize([
            $this->substep,
            (function () {
                if (!$this->activation) {
                    return null;
                }

                return [
                    $this->activation->getExpression(),
                    $this->activation->getAllConditions(),
                ];
            })(),
            $this->next ? $this->next->hash() : null,
            (function () {
                if (!$this->divergence) {
                    return null;
                }

                return array_map(function (DivergenceStepDefinition $divergenceStepDefinition) {
                    return $divergenceStepDefinition->hash();
                }, $this->divergence);
            })(),
        ]);
    }
}
