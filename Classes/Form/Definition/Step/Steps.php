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

namespace Romm\Formz\Form\Definition\Step;

use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Form\Definition\AbstractFormDefinitionComponent;
use Romm\Formz\Form\Definition\Step\Step\Step;
use Romm\Formz\Form\Definition\Step\Step\StepDefinition;

class Steps extends AbstractFormDefinitionComponent
{
    /**
     * @var \Romm\Formz\Form\Definition\Step\Settings
     */
    protected $settings;

    /**
     * @var \Romm\Formz\Form\Definition\Step\Step\Step[]
     */
    protected $entries;

    /**
     * @var \Romm\Formz\Form\Definition\Step\Step\StepDefinition
     * @validate NotEmpty
     */
    protected $firstStep;

    /**
     * @return Settings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return bool
     */
    public function hasSteps()
    {
        return null !== $this->firstStep;
    }

    /**
     * @return StepDefinition
     */
    public function getFirstStepDefinition()
    {
        return $this->firstStep;
    }

    /**
     * @return Step[]
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * @param string $name
     * @return Step
     * @throws EntryNotFoundException
     */
    public function getEntry($name)
    {
        if (false === $this->hasEntry($name)) {
            // @todo convert to silent exception
//            throw EntryNotFoundException::stepEntryNotFound($name);
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
}
