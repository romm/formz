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

use Romm\ConfigurationObject\Service\Items\Parents\ParentsTrait;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Form\Definition\AbstractFormDefinitionComponent;
use Romm\Formz\Form\Definition\Step\Steps;

class StepDefinition extends AbstractFormDefinitionComponent
{
    use ParentsTrait;

    /**
     * @var string
     * @validate NotEmpty
     */
    protected $step;

    /**
     * @var \Romm\Formz\Form\Definition\Step\Step\StepDefinition
     */
    protected $next;

    /**
     * @var \Romm\Formz\Form\Definition\Step\Step\ConditionalStepDefinition[]
     */
    protected $detour;

    /**
     * @var \Romm\Formz\Form\Definition\Step\Step\ConditionalStepDefinition[]
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

        if ($this->hasDetour()) {
            foreach ($this->getDetourSteps() as $detourStep) {
                $childWeight = max($childWeight, $detourStep->getStepWeight());
            }
        }

        return $weight + $childWeight;
    }

    /**
     * @return bool
     */
    public function hasNextStep()
    {
        return null !== $this->next
            || false === empty($this->detour)
            || false === empty($this->divergence);
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
     * @return ConditionalStepDefinition[]
     */
    public function getDetourSteps()
    {
        return $this->detour;
    }

    /**
     * @return bool
     */
    public function hasDetour()
    {
        return false === empty($this->detour);
    }

    /**
     * @return bool
     */
    public function isInDetour()
    {
        return $this instanceof ConditionalStepDefinition
            || $this->hasParent(ConditionalStepDefinition::class);
    }

    public function getDetourRootStep()
    {
        if (false === $this->isInDetour()) {
            throw new \Exception('todo'); // @todo
        }

        $stepDefinition = $this;

        while ($stepDefinition->hasParent(ConditionalStepDefinition::class)) {
            $stepDefinition = $stepDefinition->getFirstParent(ConditionalStepDefinition::class);
        }

        /** @var StepDefinition $stepDefinition */
        $stepDefinition = $stepDefinition->getFirstParent(StepDefinition::class);

        return $stepDefinition->getNextStep();
    }

    /**
     * @return ConditionalStepDefinition[]
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
