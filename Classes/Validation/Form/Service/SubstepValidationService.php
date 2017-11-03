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

class SubstepValidationService
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

        $currentSubstepDefinition = $this->getCurrentSubstepDefinition();

        if ($this->dataObject->getValidatedStep() === $stepService->getCurrentStep()) {
            $nextSubstep = $this->getNextSubstep($currentSubstepDefinition);

            if (!$nextSubstep) {
                $stepService->markLastSubstepAsValidated();
            }

            if ($this->getResult()->hasErrors()) {
                $stepService->setCurrentSubstepDefinition($currentSubstepDefinition);
            } elseif ($nextSubstep) {
                $stepService->setCurrentSubstepDefinition($nextSubstep);
            }
        }
    }

    /**
     * @return SubstepDefinition
     */
    protected function getCurrentSubstepDefinition()
    {
        $stepService = FormObjectFactory::get()->getStepService($this->getFormObject());

        $firstSubstepDefinition = $this->dataObject->getValidatedStep()->getSubsteps()->getFirstSubstepDefinition();
        $currentSubstepDefinition = $firstSubstepDefinition;
        $substepDefinition = $firstSubstepDefinition;
        $substepsLevel = $stepService->getSubstepsLevel();

        while ($this->dataObject->isDummyMode() || $substepsLevel > 0) {
            $substepsLevel--;
            $substepIsActivated = true;

            if ($substepDefinition->hasActivation()) {
                $substepIsActivated = $this->getSubstepDefinitionActivationResult($substepDefinition);
            }

            if (true === $substepIsActivated) {
                $supportedFields = $substepDefinition->getSubstep()->getSupportedFields();

                foreach ($supportedFields as $supportedField) {
                    $this->formValidatorExecutor->validateField($supportedField->getField());
                }

                if ($this->getResult()->hasErrors()) {
                    $currentSubstepDefinition = $substepDefinition;
                    break;
                }
            }

            if (!$this->dataObject->isDummyMode() && $substepsLevel === 0) {
                $currentSubstepDefinition = $substepDefinition;
                break;
            }

            if (false === $substepDefinition->hasNextSubstep()) {
                break;
            }

            $substepDefinition = $substepDefinition->getNextSubstep();
        }

        return $currentSubstepDefinition;
    }

    protected function getNextSubstep(SubstepDefinition $substepDefinition)
    {
        $nextSubstep = null;

        while ($substepDefinition) {
            if (false === $substepDefinition->hasNextSubstep()) {
                break;
            } else {
                $substepDefinition = $substepDefinition->getNextSubstep();

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

        return $nextSubstep;
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
