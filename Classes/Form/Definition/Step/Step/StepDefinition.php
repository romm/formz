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

namespace Romm\Formz\Form\Definition\Step\Step;

use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Form\Definition\AbstractFormDefinitionComponent;
use Romm\Formz\Form\Definition\Condition\Activation;
use Romm\Formz\Form\Definition\Condition\ActivationInterface;
use Romm\Formz\Form\Definition\Step\Steps;

class StepDefinition extends AbstractFormDefinitionComponent
{
    /**
     * @var string
     * @validate NotEmpty
     */
    protected $step;

    /**
     * @var \Romm\Formz\Form\Definition\Condition\Activation
     * @validate Romm.Formz:Internal\ConditionIsValid
     */
    protected $activation;

    /**
     * @var \Romm\Formz\Form\Definition\Step\Step\StepDefinition
     */
    protected $next;

    /**
     * @var \Romm\Formz\Form\Definition\Step\Step\DivergenceStepDefinition[]
     */
    protected $divergence;

    /**
     * @return Step
     */
    public function getStep()
    {
        return $this->withFirstParent(
            Steps::class,
            function (Steps $steps) {
                return $steps->getEntry($this->step);
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

    public function getStepLevel()
    {
        $level = 1;

        if ($this->hasPreviousDefinition()) {
            $this->getPreviousDefinition()->alongParents(
                function ($parent) use (&$level) {
                    if ($parent instanceof self) {
                        $level += $parent->getStepWeight();
                    } elseif ($parent instanceof Steps) {
                        return false;
                    }

                    return true;
                }
            );
        }

        return $level;
    }

    public function getStepWeight()
    {
        $weight = 1;
        $childWeight = $this->hasNextStep() ? 1 : 0;

        if ($this->hasDivergence()) {
            foreach ($this->getDivergenceSteps() as $divergenceStep) {
                $childWeight = max($childWeight, $divergenceStep->getStepWeight());
            }
        }

        return $weight + $childWeight;
    }

    /**
     * @return bool
     */
    public function hasNextStep()
    {
        return null !== $this->next;
    }

    /**
     * @return StepDefinition
     * @throws EntryNotFoundException
     */
    public function getNextStep()
    {
        if (false === $this->hasNextStep()) {
            throw EntryNotFoundException::nextStepsNotFound($this);
        }

        return $this->next;
    }

    /**
     * @return bool
     */
    public function hasPreviousDefinition()
    {
        return $this->hasParent(self::class);
    }

    /**
     * @return StepDefinition
     * @throws EntryNotFoundException
     */
    public function getPreviousDefinition()
    {
        if (false === $this->hasPreviousDefinition()) {
            throw EntryNotFoundException::previousDefinitionNotFound($this);
        }

        /** @var StepDefinition $previousStepDefinition */
        $previousStepDefinition = $this->getFirstParent(self::class);

        return $previousStepDefinition;
    }

    /**
     * @return DivergenceStepDefinition[]
     */
    public function getDivergenceSteps()
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
}
