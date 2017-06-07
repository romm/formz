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

use Romm\Formz\Exceptions\InvalidArgumentTypeException;
use Romm\Formz\Middleware\Item\DefaultMiddleware;
use Romm\Formz\Middleware\Item\Step\Service\StepMiddlewareService;
use Romm\Formz\Middleware\Processor\PresetMiddlewareInterface;

/**
 * This middleware should be the last one called, as it is used to dispatch the
 * request to the next step, if there is one.
 */
class StepDispatchingMiddleware extends DefaultMiddleware implements PresetMiddlewareInterface
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
     * @see StepDispatchingMiddleware
     */
    protected function process()
    {
        $formObject = $this->getFormObject();

        if (false === $formObject->getDefinition()->hasSteps()) {
            return;
        }
        
        $formResult = $formObject->getFormResult();

        if ($formObject->formWasSubmitted()
            && false === $formResult->hasErrors()
        ) {
            if ($formObject->getCurrentSubstepDefinition()
                && false === $formObject->getCurrentSubstepDefinition()->isLast()
            ) {
                return;
            }

            /*
             * The form was submitted, and no error was found, we can safely
             * dispatch the request to the next step.
             */
            $currentStep = $this->getCurrentStep();
            $currentStepDefinition = $this->service->getStepDefinition($currentStep);

            // Saving submitted form data for further usage.
            $this->service->markStepAsValidated($currentStepDefinition, $this->getFormRawValues());
            $this->service->addValidatedFields($formResult->getValidatedFields());

            $nextStep = null;

            if ($currentStepDefinition->hasNextStep()) {
                $nextStep = $this->service->getNextStepDefinition($currentStepDefinition, true);
            }

            if ($nextStep) {
                $this->service->moveForwardToStep($nextStep, $this->redirect());
            }
        }
    }

    /**
     * Fetches the raw values sent in the request.
     *
     * @return array
     * @throws InvalidArgumentTypeException
     */
    protected function getFormRawValues()
    {
        $formName = $this->getFormObject()->getName();
        $formArray = null;

        if ($this->getRequest()->hasArgument($formName)) {
            /** @var array $formArray */
            $formArray = $this->getRequest()->getArgument($formName);
        }

        if (false === is_array($formArray)) {
            throw InvalidArgumentTypeException::formArgumentNotArray($this->getFormObject(), $formArray);
        }

        return $formArray;
    }
}
