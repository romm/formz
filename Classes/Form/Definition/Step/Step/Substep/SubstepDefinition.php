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
use Romm\Formz\Form\Definition\Step\Step\Step;

class SubstepDefinition extends AbstractFormDefinitionComponent
{
    /**
     * @var string
     * @validate NotEmpty
     */
    protected $substep;

    /**
     * @var \Romm\Formz\Form\Definition\Step\Step\Substep\SubstepDefinition
     */
    protected $next;

    /**
     * @var \Romm\Formz\Form\Definition\Step\Step\Substep\ConditionalSubstepDefinition[]
     */
    protected $detour;

    /**
     * @var \Romm\Formz\Form\Definition\Step\Step\Substep\ConditionalSubstepDefinition[]
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

    public function getUniqueIdentifier()
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

        return $this->getSubstep()->getIdentifier() . '-' . $level;
    }

    /**
     * @todo
     *
     * @return bool
     */
    public function isLast()
    {
        return false === $this->hasNextSubsteps()
            && false === $this->hasDetour()
            && false === $this->hasDivergence();
    }

    /**
     * @return bool
     */
    public function hasNextSubsteps()
    {
        return null !== $this->next;
    }

    /**
     * @return SubstepDefinition
     */
    public function getNextSubsteps()
    {
        if (false === $this->hasNextSubsteps()) {
            throw new \Exception('todo'); // @todo
        }

        return $this->next;
    }

    /**
     * @return ConditionalSubstepDefinition[]
     */
    public function getDetourSubsteps()
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
     * @return ConditionalSubstepDefinition[]
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
}
