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
use Romm\Formz\Middleware\Item\FormValidation\FormValidationSignal;
use Romm\Formz\Middleware\Item\Step\Service\StepMiddlewareService;
use Romm\Formz\Middleware\Signal\Before;

/**
 * This middleware will fetch the current step in the form, based on the request
 * context and the steps definition in the form configuration.
 *
 * It will check if the user has the right to stand on this step; if not, a loop
 * on all previous steps is done to determine the first valid step: the request
 * is then redirected to this step.
 */
class StepFetchingMiddleware extends AbstractMiddleware implements Before, FormValidationSignal
{
    /**
     * @var int
     */
    protected $priority = self::PRIORITY_STEP;

    /**
     * @var StepMiddlewareService
     */
    protected $service;

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
    public function before(Arguments $arguments)
    {
        $formObject = $this->getFormObject();
        $this->service->reset($formObject, $this->getRequest());

        if (false === $formObject->getDefinition()->hasSteps()) {
            return;
        }

        $currentStep = $this->getCurrentStep();
        $stepDefinition = $this->service->getStepDefinition($currentStep);

        if ($currentStep
            && false === $this->service->stepIsValid($stepDefinition)
        ) {
            /*
             * The user has no right to stand on the current step, a previous
             * valid step is determined, and the user is redirected to it.
             */
            $stepToRedirect = $this->service->getFirstInvalidStep($currentStep);

            if ($stepToRedirect instanceof StepDefinition) {
                $this->service->redirectToStep($stepToRedirect->getStep(), $this->redirect());
            }
        }
    }
}
