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

namespace Romm\Formz\Middleware\Item\Step;

use Romm\Formz\Form\Definition\Step\Step\Step;
use Romm\Formz\Form\FormObject\FormObjectFactory;
use Romm\Formz\Form\FormObject\Service\FormObjectSteps;
use Romm\Formz\Middleware\Argument\Arguments;
use Romm\Formz\Middleware\Item\AbstractMiddleware;
use Romm\Formz\Middleware\Item\FormInjection\FormInjectionSignal;
use Romm\Formz\Middleware\Item\Step\Service\StepMiddlewareService;
use Romm\Formz\Middleware\Processor\PresetMiddlewareInterface;
use Romm\Formz\Middleware\Scope\FieldValidationScope;
use Romm\Formz\Middleware\Scope\ReadScope;
use Romm\Formz\Middleware\Signal\After;
use Romm\Formz\ViewHelpers\Step\PreviousLinkViewHelper;

/**
 * @todo
 */
class PreviousStepMiddleware extends AbstractMiddleware implements After, FormInjectionSignal, PresetMiddlewareInterface
{
    /**
     * @var int
     */
    protected $priority = self::PRIORITY_STEP_FETCHING + 100;

    /**
     * @var Step
     */
    protected $currentStep;

    /**
     * @var FormObjectSteps
     */
    protected $stepService;

    /**
     * @var StepMiddlewareService
     */
    protected $service;

    /**
     * @var array
     */
    protected static $defaultScopesBlackList = [ReadScope::class, FieldValidationScope::class];

    /**
     * Inject the step service.
     */
    public function initializeMiddleware()
    {
        $this->service = StepMiddlewareService::get();
    }

    /**
     * @see PreviousStepMiddleware
     *
     * @param Arguments $arguments
     */
    public function after(Arguments $arguments)
    {
        $formObject = $this->getFormObject();
        $this->service->reset($formObject, $this->getRequest());

        if (false === $formObject->getDefinition()->hasSteps()) {
            return;
        }

        $this->currentStep = $this->getCurrentStep();
        $this->stepService = FormObjectFactory::get()->getStepService($this->getFormObject());

        if ($this->currentStep) {
            if ($this->getRequest()->hasArgument(PreviousLinkViewHelper::PREVIOUS_LINK_PARAMETER)) {
                $this->redirectToPreviousStep();
            } else {
                $this->handleRedirectionSubstep();
            }
        }
    }

    protected function redirectToPreviousStep()
    {
        $stepDefinition = $this->service->getStepDefinition($this->currentStep);

        if ($this->currentStep->hasSubsteps()) {
            $substepsLevel = $this->stepService->getSubstepsLevel();

            if ($substepsLevel > 1) {
                $substepDefinition = $this->currentStep->getSubsteps()->getFirstSubstepDefinition();

                while ($substepDefinition) {
                    $nextSubstepDefinition = $this->service->getNextSubstepDefinition($substepDefinition);

                    if (!$nextSubstepDefinition
                        || $nextSubstepDefinition->getLevel() >= $substepsLevel
                    ) {
                        break;
                    }

                    $substepDefinition = $nextSubstepDefinition;
                }

                $this->stepService->setCurrentSubstepDefinition($substepDefinition);
                $this->stepService->setSubstepsLevel($substepDefinition->getLevel());

                return;
            }
        }

        if ($stepDefinition->hasPreviousDefinition()) {
            $this->service->redirectToStep(
                $stepDefinition->getPreviousDefinition()->getStep(),
                $this->redirect()->withArguments(['fz-last-substep' => true])
            );
        }
    }

    protected function handleRedirectionSubstep()
    {
        if ($this->currentStep->hasSubsteps()
            && $this->getRequest()->hasArgument('fz-last-substep')
        ) {
            $substepDefinition = $this->currentStep->getSubsteps()->getFirstSubstepDefinition();

            do {
                $nextSubstepDefinition = $this->service->getNextSubstepDefinition($substepDefinition);

                if ($nextSubstepDefinition) {
                    $substepDefinition = $nextSubstepDefinition;
                }
            } while ($nextSubstepDefinition);

            $this->stepService->setCurrentSubstepDefinition($substepDefinition);
        }
    }
}
