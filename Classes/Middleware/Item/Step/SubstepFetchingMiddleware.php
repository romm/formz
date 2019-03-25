<?php
declare(strict_types=1);

namespace Romm\Formz\Middleware\Item\Step;

use Romm\Formz\Form\Definition\Step\Step\Step;
use Romm\Formz\Form\FormObject\FormObjectFactory;
use Romm\Formz\Form\FormObject\Service\FormObjectSteps;
use Romm\Formz\Middleware\Argument\Arguments;
use Romm\Formz\Middleware\Item\AbstractMiddleware;
use Romm\Formz\Middleware\Item\FormInjection\FormInjectionSignal;
use Romm\Formz\Middleware\Item\Step\Service\StepMiddlewareService;
use Romm\Formz\Middleware\Processor\PresetMiddlewareInterface;
use Romm\Formz\Middleware\Signal\After;

class SubstepFetchingMiddleware extends AbstractMiddleware implements After, FormInjectionSignal, PresetMiddlewareInterface
{
    /**
     * @var int
     */
    protected $priority = self::PRIORITY_STEP_FETCHING + 150;

    /**
     * @var Step
     */
    protected $currentStep;

    /**
     * @var StepMiddlewareService
     */
    protected $service;

    /**
     * @var FormObjectSteps
     */
    protected $stepService;

    /**
     * Inject the step service.
     */
    public function initializeMiddleware()
    {
        $this->service = StepMiddlewareService::get();
    }

    /**
     * @param Arguments $arguments
     */
    public function after(Arguments $arguments)
    {
        if ($this->getFormObject()->formWasSubmitted()) {
            return;
        }

        $formObject = $this->getFormObject();

        if (false === $formObject->getDefinition()->hasSteps()) {
            return;
        }

        $this->currentStep = $this->getCurrentStep();

        if (null === $this->currentStep
            || false === $this->currentStep->hasSubsteps()
        ) {
            return;
        }

        $this->stepService = FormObjectFactory::get()->getStepService($formObject);

        $this->fetchCurrentSubstep();
    }

    /**
     * Fetches the current substep that should be displayed (the first
     * substep(s) may have an activation condition).
     */
    private function fetchCurrentSubstep()
    {
        $this->service->reset($this->getFormObject(), $this->getRequest());

        $substepDefinition = $this->service->findFirstSubstepDefinition($this->currentStep);

        $this->stepService->setCurrentSubstepDefinition($substepDefinition);
    }
}
