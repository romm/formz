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
use Romm\Formz\Error\FormResult;
use Romm\Formz\Form\Definition\Step\Step\Step;
use Romm\Formz\Form\Definition\Step\Step\StepDefinition;
use Romm\Formz\Form\Definition\Step\Step\Substep\SubstepDefinition;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Form\FormObject\FormObjectFactory;
use Romm\Formz\Form\FormObject\Service\Step\FormStepPersistence;
use Romm\Formz\Middleware\Request\Redirect;
use Romm\Formz\Service\Traits\SelfInstantiateTrait;
use Romm\Formz\Validation\Form\DataObject\FormValidatorDataObject;
use Romm\Formz\Validation\Form\FormValidatorExecutor;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Request;

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
     * @var Request
     */
    protected $request;

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
     * @param Request $request
     */
    public function reset(FormObject $formObject, Request $request)
    {
        $this->formObject = $formObject;
        $this->request = $request;

        $this->persistence = FormObjectFactory::get()->getStepService($formObject)->getStepPersistence();

        $this->validationService = GeneralUtility::makeInstance(StepMiddlewareValidationService::class, $this);
    }

    /**
     * @param Step $currentStep
     * @return StepDefinition|null
     */
    public function getNextStep(Step $currentStep)
    {
        /*
         * The form was submitted, and no error was found, we can safely
         * dispatch the request to the next step.
         */
        $currentStepDefinition = $this->getStepDefinition($currentStep);

        $this->validationService->markStepAsValidated($currentStepDefinition);
        // @todo tmp-delete?
//        // Saving submitted form data for further usage.
//        $this->markStepAsValidated($currentStepDefinition, $this->getFormRawValues());
        $this->addValidatedFields($this->formObject->getFormResult()->getValidatedFields());

        $nextStep = null;

        if ($currentStepDefinition->hasNextStep()) {
            $nextStep = $this->getNextStepDefinition($currentStepDefinition);
        }

        return $nextStep;
    }

    // @todo tmp-delete?
//    /**
//     * Saves the submitted values in the metadata, for the given step.
//     *
//     * @param Step $currentStep
//     */
//    public function saveStepFormValues(Step $currentStep)
//    {
//        $this->persistence->addStepFormValues($this->getStepDefinition($currentStep), $this->getFormRawValues());
//    }
//
//    /**
//     * Fetches the raw values sent in the request.
//     *
//     * @return array
//     * @throws InvalidArgumentTypeException
//     */
//    protected function getFormRawValues()
//    {
//        $formName = $this->getFormObject()->getName();
//        $formArray = null;
//
//        if ($this->request->hasArgument($formName)) {
//            /** @var array $formArray */
//            $formArray = $this->request->getArgument($formName);
//
//            if (false === is_array($formArray)) {
//                throw InvalidArgumentTypeException::formArgumentNotArray($this->getFormObject(), $formArray);
//            }
//        } else {
//            $formArray = [];
//        }
//
//        return $formArray;
//    }
//
//    /**
//     * @see \Romm\Formz\Middleware\Item\Step\Service\StepMiddlewareValidationService::markStepAsValidated()
//     *
//     * @param StepDefinition $stepDefinition
//     * @param array          $formValues
//     */
//    public function markStepAsValidated(StepDefinition $stepDefinition, array $formValues)
//    {
//        $this->validationService->markStepAsValidated($stepDefinition, $formValues);
//    }

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

        if ($step->hasDivergence()) {
            $divergenceSteps = $step->getDivergenceSteps();

            foreach ($divergenceSteps as $divergenceStep) {
                if (true === $this->getStepDefinitionConditionResult($divergenceStep)) {
                    $nextStep = $divergenceStep;
                    break;
                }
            }
        }

        if (null === $nextStep) {
            while ($step->hasNextStep()) {
                $step = $step->getNextStep();

                if ($step->hasActivation()) {
                    if (true === $this->getStepDefinitionConditionResult($step)) {
                        $nextStep = $step;
                        break;
                    } else {
                        $this->persistence->removeStep($step);
                    }
                } else {
                    $nextStep = $step;
                    break;
                }
            }
        }

        return $nextStep;
    }

    public function getNextSubstepDefinition(SubstepDefinition $substepDefinition)
    {
        $nextSubstep = null;

        if ($substepDefinition->hasDivergence()) {
            $divergenceSteps = $substepDefinition->getDivergenceSubsteps();

            foreach ($divergenceSteps as $divergenceStep) {
                if (true === $this->getSubstepDefinitionConditionResult($divergenceStep)) {
                    $nextSubstep = $divergenceStep;
                    break;
                }
            }
        }

        if (null === $nextSubstep) {
            while ($substepDefinition->hasNextSubstep()) {
                $substepDefinition = $substepDefinition->getNextSubstep();

                if ($substepDefinition->hasActivation()) {
                    if (true === $this->getSubstepDefinitionConditionResult($substepDefinition)) {
                        $nextSubstep = $substepDefinition;
                        break;
                    }
                } else {
                    $nextSubstep = $substepDefinition;
                    break;
                }
            }
        }

        return $nextSubstep;
    }

    public function findSubstepDefinition(Step $step, callable $callback)
    {
        return $this->findSubstepDefinitionRecursive($step->getSubsteps()->getFirstSubstepDefinition(), $callback);
    }

    protected function findSubstepDefinitionRecursive(SubstepDefinition $substepDefinition, callable $callback)
    {
        $result = $callback($substepDefinition);

        if (true === $result) {
            return $substepDefinition;
        }

        $substepDefinition = $this->getNextSubstepDefinition($substepDefinition);

        return $substepDefinition
            ? $this->findSubstepDefinitionRecursive($substepDefinition, $callback)
            : null;
    }

    /**
     * @param StepDefinition $stepDefinition
     * @param Redirect       $redirect
     */
    public function moveForwardToStep(StepDefinition $stepDefinition, Redirect $redirect)
    {
        $this->persistence->setStepLevel($stepDefinition);
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
     * @param StepDefinition $stepDefinition
     * @return bool
     */
    public function getStepDefinitionConditionResult(StepDefinition $stepDefinition)
    {
        $conditionProcessor = ConditionProcessorFactory::getInstance()->get($this->getFormObject());
        $tree = $conditionProcessor->getActivationConditionTreeForStep($stepDefinition);
        $todo = new FormValidatorExecutor(new FormValidatorDataObject($this->getFormObject(), new FormResult(), true)); // @todo
        $dataObject = new PhpConditionDataObject($this->getFormObject()->getForm(), $todo);

        return $tree->getPhpResult($dataObject);
    }

    /**
     * @param SubstepDefinition $substepDefinition
     * @return bool
     */
    public function getSubstepDefinitionConditionResult(SubstepDefinition $substepDefinition)
    {
        $conditionProcessor = ConditionProcessorFactory::getInstance()->get($this->getFormObject());
        $tree = $conditionProcessor->getActivationConditionTreeForSubstep($substepDefinition);
        $todo = new FormValidatorExecutor(new FormValidatorDataObject($this->getFormObject(), new FormResult(), true)); // @todo
        $dataObject = new PhpConditionDataObject($this->getFormObject()->getForm(), $todo);

        return $tree->getPhpResult($dataObject);
    }

    /**
     * @param Step $step
     * @return StepDefinition|null
     */
    public function getStepDefinition(Step $step)
    {
        return $this->findStepDefinition($step, $this->getFirstStepDefinition());
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
