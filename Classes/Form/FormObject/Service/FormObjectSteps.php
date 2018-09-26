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

namespace Romm\Formz\Form\FormObject\Service;

use Romm\Formz\Core\Core;
use Romm\Formz\Form\Definition\Step\Step\Step;
use Romm\Formz\Form\Definition\Step\Step\StepDefinition;
use Romm\Formz\Form\Definition\Step\Step\Substep\SubstepDefinition;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Form\FormObject\Service\Step\FormStepPersistence;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FormObjectSteps
{
    const METADATA_STEP_PERSISTENCE_KEY = 'core.formStepPersistence';

    /**
     * @var FormObject
     */
    protected $formObject;

    /**
     * Step persistence is saved in the form metadata.
     *
     * It allows having essential information about the form steps whenever it
     * is needed: submitted form values, as well as steps that were already
     * validated.
     *
     * @var FormStepPersistence
     */
    protected $stepPersistence;

    /**
     * @var Step
     */
    protected $currentStep;

    /**
     * @todo ADD REQUEST CONTEXT INSIDE THE FORM OBJECT ?!
     *
     * @var string
     */
    protected $currentHash;

    /**
     * @var SubstepDefinition
     */
    protected $currentSubstepDefinition;

    /**
     * @var int
     */
    protected $substepsLevel = 1;

    /**
     * @var bool
     */
    protected $lastSubstepValidated = false;

    /**
     * @param FormObject $formObject
     */
    public function __construct(FormObject $formObject)
    {
        $this->formObject = $formObject;
    }

    /**
     * This function will search among the registered steps to find the one that
     * has the same controller parameters.
     *
     * It is also possible not to find any step, in this case `null` is
     * returned.
     *
     * @todo: memoization with request spl object storage
     *
     * @param string $extensionName
     * @param string $controllerName
     * @param string $actionName
     */
    public function fetchCurrentStep($extensionName, $controllerName, $actionName)
    {
        $this->currentHash = "$extensionName:$controllerName->$actionName";

        if (null !== $this->currentStep[$this->currentHash]) {
            return;
        }

        $this->currentStep[$this->currentHash] = false;

        $definition = $this->formObject->getDefinition();

        if ($definition->hasSteps()) {
            foreach ($definition->getSteps()->getEntries() as $step) {
                $data = [
                    $step->getExtension() => $extensionName,
                    $step->getController() => $controllerName
                ];

                foreach ($data as $stepData => $requestData) {
                    if (false === empty($stepData)
                        && $stepData !== $requestData
                    ) {
                        continue 2;
                    }
                }

                if ($step->getPageUid()
                    && $step->getPageUid() !== Core::get()->getPageController()->id
                ) {
                    continue;
                }

                $actionList = $step->getAuthorizedActions();

                if (false === in_array($actionName, $actionList)) {
                    continue;
                }

                if ($this->currentStep[$this->currentHash] instanceof Step) {
                    throw new \Exception('todo'); // @todo
                }

                $this->currentStep[$this->currentHash] = $step;
            }
        }
    }

    /**
     * @param Step $step
     */
    public function setCurrentStep(Step $step)
    {
        $this->currentStep[$this->currentHash] = $step;
    }

    /**
     * @return Step|null
     */
    public function getCurrentStep()
    {
        if (null === $this->currentStep[$this->currentHash]) {
            throw new \Exception('todo'); // @todo
        }

        return $this->currentStep[$this->currentHash] ?: null;
    }

    /**
     * @param Step $step
     * @return StepDefinition|null
     */
    public function getStepDefinition(Step $step)
    {
        return $this->findStepDefinition($step, $this->formObject->getDefinition()->getSteps()->getFirstStepDefinition());
    }

    /**
     * @param Step           $step
     * @param StepDefinition $stepDefinition
     * @return StepDefinition|null
     */
    protected function findStepDefinition(Step $step, StepDefinition $stepDefinition)
    {
        if ($stepDefinition->getStep() === $step) {
            return $stepDefinition;
        }

        if ($stepDefinition->hasNextStep()) {
            $result = $this->findStepDefinition($step, $stepDefinition->getNextStep());

            if ($result instanceof StepDefinition) {
                return $result;
            }
        }

        if ($stepDefinition->hasDivergence()) {
            foreach ($stepDefinition->getDivergenceSteps() as $divergenceStep) {
                $result = $this->findStepDefinition($step, $divergenceStep);

                if ($result instanceof StepDefinition) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * Fetches the step persistence object for the form, which may have been
     * stored in the form metadata.
     *
     * If the form object hash did change since the persistence object was saved
     * it is "refreshed" with the new hash (some data are also deleted as they
     * are no longer considered as valid).
     *
     * @return FormStepPersistence
     */
    public function getStepPersistence()
    {
        // @todo check configuration has steps or fatal error
        if (null === $this->stepPersistence) {
            $objectHash = $this->formObject->getObjectHash();
            $metadata = $this->formObject->getFormMetadata();

            if ($metadata->has(self::METADATA_STEP_PERSISTENCE_KEY)) {
                $this->stepPersistence = $metadata->get(self::METADATA_STEP_PERSISTENCE_KEY);

                if (false === $this->stepPersistence instanceof FormStepPersistence) {
                    unset($this->stepPersistence);
                } elseif ($objectHash !== $this->stepPersistence->getObjectHash()) {
                    $this->stepPersistence->refreshObjectHash($objectHash);
                }
            }

            if (null === $this->stepPersistence) {
                $this->stepPersistence = GeneralUtility::makeInstance(FormStepPersistence::class, $objectHash);
                $metadata->set(self::METADATA_STEP_PERSISTENCE_KEY, $this->stepPersistence);
            }
        }

        return $this->stepPersistence;
    }

    /**
     * @return SubstepDefinition
     */
    public function getCurrentSubstepDefinition()
    {
        return $this->currentSubstepDefinition ?: $this->getCurrentStep()->getSubsteps()->getFirstSubstepDefinition();
    }

    /**
     * @param SubstepDefinition $currentSubstepDefinition
     */
    public function setCurrentSubstepDefinition(SubstepDefinition $currentSubstepDefinition)
    {
        $this->currentSubstepDefinition = $currentSubstepDefinition;
    }

    /**
     * @param int $level
     */
    public function setSubstepsLevel($level)
    {
        $this->substepsLevel = max(1, (int)$level);
    }

    /**
     * @return int
     */
    public function getSubstepsLevel()
    {
        return $this->substepsLevel;
    }

    /**
     * @todo
     */
    public function markLastSubstepAsValidated()
    {
        $this->lastSubstepValidated = true;
    }

    /**
     * @return bool
     */
    public function lastSubstepWasValidated()
    {
        return $this->lastSubstepValidated;
    }
}
