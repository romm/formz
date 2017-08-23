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

namespace Romm\Formz\Form\FormObject\Service\Step;

use Romm\Formz\Form\Definition\Step\Step\Step;
use Romm\Formz\Form\Definition\Step\Step\StepDefinition;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * This object is stored in a form metadata, and contains important information
 * about the form steps:
 * - Which steps were already validated;
 * - Form data that were submitted by the user at every step.
 *
 * Data consistency of this object is assured by the form object hash (which is
 * mainly calculated from the form configuration): if the hash changes (if the
 * form configuration changes), the steps that were validated are no longer
 * considered as valid, and will need to be validated again.
 */
class FormStepPersistence
{
    /**
     * @var string
     */
    protected $objectHash;

    /**
     * @var array
     */
    protected $validatedSteps = [];

    /**
     * @var array
     */
    protected $stepLevels = [];

    /**
     * @var array
     */
    protected $stepsFormValues = [];

    /**
     * @var array
     */
    protected $validatedFields = [];

    /**
     * @param string $configurationHash
     */
    public function __construct($configurationHash)
    {
        $this->objectHash = $configurationHash;
    }

    /**
     * @param StepDefinition $stepDefinition
     */
    public function markStepAsValidated(StepDefinition $stepDefinition)
    {
        $identifier = $stepDefinition->getStep()->getIdentifier();

        $this->validatedSteps[$identifier] = $identifier;
        $this->stepLevels[$stepDefinition->getStepLevel()] = $identifier;
    }

    /**
     * @param StepDefinition $stepDefinition
     */
    public function removeStep(StepDefinition $stepDefinition)
    {
        $identifier = $stepDefinition->getStep()->getIdentifier();

        unset($this->validatedSteps[$identifier]);
        unset($this->stepLevels[$stepDefinition->getStepLevel()]);
        unset($this->stepsFormValues[$identifier]);
    }

    /**
     * @param Step $step
     * @return bool
     */
    public function stepWasValidated(Step $step)
    {
        return in_array($step->getIdentifier(), $this->validatedSteps);
    }

    /**
     * @param StepDefinition $stepDefinition
     */
    public function setStepLevel(StepDefinition $stepDefinition)
    {
        $this->stepLevels[$stepDefinition->getStepLevel()] = $stepDefinition->getStep()->getIdentifier();
    }

    /**
     * @param int $level
     * @return bool
     */
    public function hasStepIdentifierAtLevel($level)
    {
        return isset($this->stepLevels[$level]);
    }

    /**
     * @param int $level
     * @return string
     */
    public function getStepIdentifierAtLevel($level)
    {
        if (false === $this->hasStepIdentifierAtLevel($level)) {
            throw new \Exception('todo'); // @todo
        }

        return $this->stepLevels[$level];
    }

    /**
     * @param StepDefinition $stepDefinition
     * @param array          $formValues
     */
    public function addStepFormValues(StepDefinition $stepDefinition, array $formValues)
    {
        $identifier = $stepDefinition->getStep()->getIdentifier();

        if (false === isset($this->stepsFormValues[$identifier])) {
            $this->stepsFormValues[$identifier] = $formValues;
        } else {
            ArrayUtility::mergeRecursiveWithOverrule($this->stepsFormValues[$identifier], $formValues);
        }
    }

    /**
     * @param StepDefinition $stepDefinition
     * @return bool
     */
    public function hasStepFormValues(StepDefinition $stepDefinition)
    {
        return true === array_key_exists($stepDefinition->getStep()->getIdentifier(), $this->stepsFormValues);
    }

    /**
     * @param StepDefinition $stepDefinition
     * @return array
     */
    public function getStepFormValues(StepDefinition $stepDefinition)
    {
        if (false === $this->hasStepFormValues($stepDefinition)) {
            throw new \Exception('todo'); // @todo
        }

        return $this->stepsFormValues[$stepDefinition->getStep()->getIdentifier()];
    }

    /**
     * @return array
     */
    public function getMergedFormValues()
    {
        $formValues = [];

        foreach ($this->stepsFormValues as $stepFormValues) {
            unset($stepFormValues['__identity']);
            $formValues = array_merge($formValues, $stepFormValues);
        }

        return $formValues;
    }

    /**
     * @param array $validatedFields
     */
    public function addValidatedFields(array $validatedFields)
    {
        $this->validatedFields = array_merge($this->validatedFields, $validatedFields);
    }

    /**
     * @return bool
     */
    public function hasData()
    {
        return false === empty($this->validatedSteps)
            && false === empty($this->stepsFormValues);
    }

    /**
     * @return string
     */
    public function getObjectHash()
    {
        return $this->objectHash;
    }

    /**
     * @param string $hash
     */
    public function refreshObjectHash($hash)
    {
        $this->objectHash = $hash;
        $this->resetValidationData();
    }

    /**
     * @todo
     */
    public function resetValidationData()
    {
        $this->validatedSteps = [];
        $this->stepLevels = [];
    }
}
