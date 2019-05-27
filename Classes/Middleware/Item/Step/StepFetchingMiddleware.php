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

use Romm\Formz\Form\Definition\Step\Step\StepDefinition;
use Romm\Formz\Middleware\Argument\Arguments;
use Romm\Formz\Middleware\Item\AbstractMiddleware;
use Romm\Formz\Middleware\Item\FormInjection\FormInjectionSignal;
use Romm\Formz\Middleware\Item\Step\Service\StepMiddlewareService;
use Romm\Formz\Middleware\Processor\PresetMiddlewareInterface;
use Romm\Formz\Middleware\Scope\FieldValidationScope;
use Romm\Formz\Middleware\Scope\ReadScope;
use Romm\Formz\Middleware\Signal\After;

/**
 * This middleware will fetch the current step in the form, based on the request
 * context and the steps definition in the form configuration.
 *
 * It will check if the user has the right to stand on this step; if not, a loop
 * on all previous steps is done to determine the first valid step: the request
 * is then redirected to this step.
 */
class StepFetchingMiddleware extends AbstractMiddleware implements After, FormInjectionSignal, PresetMiddlewareInterface
{
    /**
     * @var int
     */
    protected $priority = self::PRIORITY_STEP_FETCHING;

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
     * @see StepFetchingMiddleware
     *
     * @param Arguments $arguments
     */
    public function after(Arguments $arguments)
    {
        $formObject = $this->getFormObject();

        if (false === $formObject->getDefinition()->hasSteps()) {
            return;
        }

        $currentStep = $this->getCurrentStep();

        if ($currentStep) {
            $stepDefinition = $this->service->getStepDefinition($currentStep);

            if (false === $this->service->stepIsValid($stepDefinition)) {
                /*
                 * The user has no right to stand on the current step, a previous
                 * valid step is determined, and the user is redirected to it.
                 */
                $stepToRedirect = $this->service->getFirstInvalidStep($currentStep);

                if ($stepToRedirect instanceof StepDefinition) {
                    $this->service->redirectToStep($stepToRedirect->getStep(), $this->redirect());
                }

                /**
                 * If we don't find an invalid Step we search the first not validated step
                 */
                $stepToRedirect = $this->service->getFirstNotValidatedStep($stepDefinition);
                if ($stepToRedirect instanceof StepDefinition) {
                    $this->service->redirectToStep($stepToRedirect->getStep(), $this->redirect());
                }
            }
        }
    }
}
