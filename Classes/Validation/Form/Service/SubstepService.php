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

namespace Romm\Formz\Validation\Form\Service;

use Romm\Formz\Condition\Processor\ConditionProcessorFactory;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Form\Definition\Step\Step\Substep\SubstepDefinition;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Form\FormObject\FormObjectFactory;
use Romm\Formz\Validation\Form\DataObject\FormValidatorDataObject;
use Romm\Formz\Validation\Form\FormValidatorExecutor;

class SubstepService
{
    /**
     * @var FormValidatorExecutor
     */
    protected $formValidatorExecutor;

    /**
     * @var FormValidatorDataObject
     */
    protected $dataObject;

    /**
     * @param FormValidatorExecutor   $formValidatorExecutor
     * @param FormValidatorDataObject $dataObject
     */
    public function __construct(FormValidatorExecutor $formValidatorExecutor, FormValidatorDataObject $dataObject)
    {
        $this->formValidatorExecutor = $formValidatorExecutor;
        $this->dataObject = $dataObject;
    }

    /**
     * @todo
     */
    public function handleSubsteps()
    {
        $stepService = FormObjectFactory::get()->getStepService($this->getFormObject());

        $currentSubstepDefinition = $this->aze();

        if (null !== $currentSubstepDefinition
            && $this->dataObject->getValidatedStep() === $stepService->getCurrentStep()
        ) {
            if ($this->getResult()->hasErrors()) {
                $stepService->setCurrentSubstepDefinition($currentSubstepDefinition);
            } else {
                list($nextSubstep, $substepsLevelIncrease) = $this->getNextSubstep($currentSubstepDefinition);

                if ($nextSubstep) {
                    $stepService->setCurrentSubstepDefinition($nextSubstep);
                    $stepService->setSubstepsLevel($stepService->getSubstepsLevel() + $substepsLevelIncrease);
                } else {
                    $stepService->markLastSubstepAsValidated();
                }
            }
        }
    }

    protected function aze()
    {
        $stepService = FormObjectFactory::get()->getStepService($this->getFormObject());

        $currentSubstepDefinition = null;
        $firstSubstepDefinition = $this->dataObject->getValidatedStep()->getSubsteps()->getFirstSubstepDefinition();
        $substepDefinition = $firstSubstepDefinition;
        $substepsLevel = $stepService->getSubstepsLevel();
        $stepService->setSubstepsLevel(1);
        $substepsLevelCounter = 0;

        while ($substepDefinition && $substepsLevel > 0) {
            $substepsLevel--;
            $substepsLevelCounter++;
            $phpResult = true;

            if ($substepDefinition->hasActivation()) {
                $phpResult = $this->getSubstepDefinitionActivationResult($substepDefinition);
            }

            if (true === $phpResult) {
                $supportedFields = $substepDefinition->getSubstep()->getSupportedFields();

                foreach ($supportedFields as $supportedField) {
                    $this->formValidatorExecutor->validateField($supportedField->getField());
                }
            }

            if ($substepsLevel === 0
                || $this->getResult()->hasErrors()
            ) {
                $currentSubstepDefinition = $substepDefinition;
                $stepService->setSubstepsLevel($substepsLevelCounter);
                break;
            }

            $substepDefinition = $substepDefinition->hasNextSubstep()
                ? $substepDefinition->getNextSubstep()
                : null;
        }

        return $currentSubstepDefinition;
    }

    protected function getNextSubstep(SubstepDefinition $substepDefinition)
    {
        $substepsLevelIncrease = 0;
        $nextSubstep = null;

        while ($substepDefinition) {
            if (false === $substepDefinition->hasNextSubstep()) {
                break;
            } else {
                $substepDefinition = $substepDefinition->getNextSubstep();
                $substepsLevelIncrease++;

                if (false === $substepDefinition->hasActivation()) {
                    $nextSubstep = $substepDefinition;
                    break;
                } else {
                    $phpResult = $this->getSubstepDefinitionActivationResult($substepDefinition);

                    if (true === $phpResult) {
                        $nextSubstep = $substepDefinition;
                        break;
                    }
                }
            }
        }

        return [$nextSubstep, $substepsLevelIncrease];
    }

    /**
     * @param SubstepDefinition $substepDefinition
     * @return bool
     */
    protected function getSubstepDefinitionActivationResult(SubstepDefinition $substepDefinition)
    {
        $conditionProcessor = ConditionProcessorFactory::getInstance()->get($this->getFormObject());
        $tree = $conditionProcessor->getActivationConditionTreeForSubstep($substepDefinition);
        $dataObject = new PhpConditionDataObject($this->getFormObject()->getForm(), $this->formValidatorExecutor);

        return $tree->getPhpResult($dataObject);
    }

    /**
     * @return FormObject
     */
    protected function getFormObject()
    {
        return $this->dataObject->getFormObject();
    }

    /**
     * @return FormResult
     */
    protected function getResult()
    {
        return $this->dataObject->getFormResult();
    }
}
