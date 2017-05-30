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

namespace Romm\Formz\Middleware\Item\Step\Service;

use Romm\Formz\Condition\Processor\ConditionProcessorFactory;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use Romm\Formz\Form\Definition\Step\Step\Step;
use Romm\Formz\Form\Definition\Step\Step\StepDefinition;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Form\FormObject\FormObjectFactory;
use Romm\Formz\Form\FormObject\Service\Step\FormStepPersistence;
use Romm\Formz\Middleware\Request\Redirect;
use Romm\Formz\Service\Traits\SelfInstantiateTrait;
use Romm\Formz\Validation\Validator\Form\DataObject\FormValidatorDataObject;
use Romm\Formz\Validation\Validator\Form\FormValidatorExecutor;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This service allows extended form steps manipulation.
 */
class StepMiddlewareService implements SingletonInterface
{
    use SelfInstantiateTrait;

    /**
     * @var FormObject
     */
    protected $formObject;

    /**
     * @var StepMiddlewareValidationService
     */
    protected $validationService;

    /**
     * @var FormStepPersistence
     */
    protected $persistence;

    /**
     * @param FormObject $formObject
     */
    public function reset(FormObject $formObject)
    {
        $this->formObject = $formObject;

        $proxy = FormObjectFactory::get()->getProxy($formObject->getForm());
        $this->persistence = $proxy->getStepPersistence();

        $this->validationService = GeneralUtility::makeInstance(StepMiddlewareValidationService::class, $this);
    }

    /**
     * @see \Romm\Formz\Middleware\Item\Step\Service\StepMiddlewareValidationService::markStepAsValidated
     *
     * @param StepDefinition $stepDefinition
     * @param array          $formValues
     */
    public function markStepAsValidated(StepDefinition $stepDefinition, array $formValues)
    {
        $this->validationService->markStepAsValidated($stepDefinition, $formValues);
    }

    /**
     * @see \Romm\Formz\Middleware\Item\Step\Service\StepMiddlewareValidationService::addValidatedFields
     *
     * @param array $validatedFields
     */
    public function addValidatedFields(array $validatedFields)
    {
        $this->validationService->addValidatedFields($validatedFields);
    }

    /**
     * @see \Romm\Formz\Middleware\Item\Step\Service\StepMiddlewareValidationService::stepDefinitionIsValid
     *
     * @param StepDefinition $stepDefinition
     * @return bool
     */
    public function stepIsValid(StepDefinition $stepDefinition)
    {
        return $this->validationService->stepDefinitionIsValid($stepDefinition);
    }

    /**
     * @see \Romm\Formz\Middleware\Item\Step\Service\StepMiddlewareValidationService::getFirstInvalidStep
     *
     * @param Step $step
     * @return StepDefinition|null
     */
    public function getFirstInvalidStep(Step $step)
    {
        return $this->validationService->getFirstInvalidStep($step);
    }

    /**
     * @param StepDefinition $step
     * @return StepDefinition
     */
    public function getNextStepDefinition(StepDefinition $step)
    {
        $nextStep = null;

        if ($step->hasDetour()) {
            $detourSteps = $step->getDetourSteps();
            $conditionProcessor = ConditionProcessorFactory::getInstance()->get($this->getFormObject());

            foreach ($detourSteps as $detourStep) {
                $tree = $conditionProcessor->getActivationConditionTreeForStep($detourStep);
                $todo = new FormValidatorExecutor($this->getFormObject(), new FormValidatorDataObject());
                $dataObject = new PhpConditionDataObject($this->getFormObject()->getForm(), $todo);
                $phpResult = $tree->getPhpResult($dataObject);

                if (true === $phpResult) {
                    $nextStep = $detourStep;
                    break;
                }
            }
        }

        if (null === $nextStep) {
            $nextStep = $step->getNextStep();
        }

        return $nextStep;
    }

    /**
     * @param StepDefinition $stepDefinition
     * @param Redirect       $redirect
     */
    public function moveForwardToStep(StepDefinition $stepDefinition, Redirect $redirect)
    {
        $this->getStepPersistence()->setStepLevel($stepDefinition);
        $this->redirectToStep($stepDefinition->getStep(), $redirect);
    }

    /**
     * Redirects the current request to the given step.
     *
     * @param Step     $step
     * @param Redirect $redirect
     */
    public function redirectToStep(Step $step, Redirect $redirect)
    {
        $redirect->toPage($step->getPageUid())
            ->toExtension($step->getExtension())
            ->toController($step->getController())
            ->toAction($step->getAction())
            ->withArguments([
                'fz-hash' => [
                    $this->formObject->getName() => $this->formObject->getFormHash()
                ]
            ])
            ->dispatch();
    }

    /**
     * @param Step $step
     * @return StepDefinition|null
     */
    public function getStepDefinition(Step $step)
    {
        return $this->findStep($step, $this->getFirstStepDefinition());
    }

    /**
     * @param Step           $step
     * @param StepDefinition $stepDefinition
     * @return StepDefinition|null
     */
    protected function findStep(Step $step, StepDefinition $stepDefinition)
    {
        if ($stepDefinition->getStep() === $step) {
            return $stepDefinition;
        }

        if ($stepDefinition->hasNextStep()) {
            $result = $this->findStep($step, $stepDefinition->getNextStep());

            if ($result instanceof StepDefinition) {
                return $result;
            }
        }

        if ($stepDefinition->hasDetour()) {
            foreach ($stepDefinition->getDetourSteps() as $detourStep) {
                $result = $this->findStep($step, $detourStep);

                if ($result instanceof StepDefinition) {
                    return $result;
                }
            }
        }

        if ($stepDefinition->hasDivergence()) {
            foreach ($stepDefinition->getDivergenceSteps() as $divergenceStep) {
                $result = $this->findStep($step, $divergenceStep);

                if ($result instanceof StepDefinition) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * @return FormStepPersistence
     */
    public function getStepPersistence()
    {
        return $this->persistence;
    }

    /**
     * @return FormObject
     */
    public function getFormObject()
    {
        return $this->formObject;
    }

    /**
     * @return StepDefinition
     */
    public function getFirstStepDefinition()
    {
        return $this->formObject->getDefinition()->getSteps()->getFirstStepDefinition();
    }
}
