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

use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Form\Definition\AbstractFormDefinitionComponent;

class Substeps extends AbstractFormDefinitionComponent
{
    /**
     * @var \Romm\Formz\Form\Definition\Step\Step\Substep\Substep[]
     */
    protected $entries;

    /**
     * @var \Romm\Formz\Form\Definition\Step\Step\Substep\SubstepDefinition
     * @validate NotEmpty
     */
    protected $firstSubstep;

    /**
     * @return SubstepDefinition
     */
    public function getFirstSubstepDefinition()
    {
        return $this->firstSubstep;
    }

    /**
     * @return Substep[]
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * @param string $name
     * @return Substep
     * @throws EntryNotFoundException
     */
    public function getEntry($name)
    {
        if (false === $this->hasEntry($name)) {
            throw new \Exception('todo'); // @todo
        }

        return $this->entries[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasEntry($name)
    {
        return isset($this->entries[$name]);
    }

    /**
     * @todo
     */
    public function getMaxLevel()
    {
        return $this->getMaxLevelRecursive($this->firstSubstep);
    }

    /**
     * @param SubstepDefinition $substepDefinition
     * @return int
     */
    protected function getMaxLevelRecursive(SubstepDefinition $substepDefinition)
    {
        $maxLevel = $substepDefinition->getLevel();

        if ($substepDefinition->hasNextSubstep()) {
            $maxLevel = $this->getMaxLevelRecursive($substepDefinition->getNextSubstep());
        }

        if ($substepDefinition->hasDivergence()) {
            foreach ($substepDefinition->getDivergenceSubsteps() as $divergenceSubstep) {
                $maxLevel = max($maxLevel, $this->getMaxLevelRecursive($divergenceSubstep));
            }
        }

        return $maxLevel;
    }
}
