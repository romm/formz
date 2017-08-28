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

use Romm\Formz\Form\FormObject\FormObjectFactory;
use Romm\Formz\Middleware\Item\DefaultMiddleware;
use Romm\Formz\Middleware\Item\Step\Service\StepMiddlewareService;
use Romm\Formz\Middleware\Processor\PresetMiddlewareInterface;
use Romm\Formz\Middleware\Processor\RemoveFromSingleFieldValidationContext;
use Romm\Formz\Middleware\Signal\SendsMiddlewareSignal;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This middleware should be the last one called, as it is used to dispatch the
 * request to the next step, if there is one.
 */
class StepDispatchingMiddleware extends DefaultMiddleware implements PresetMiddlewareInterface, SendsMiddlewareSignal, RemoveFromSingleFieldValidationContext
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
            $currentStep = $this->getCurrentStep();

            if ($currentStep) {
                // @todo tmp-delete?
//                /*
//                 * No error during the validation : the submitted form values
//                 * are saved in the step metadata.
//                 */
//                $this->service->saveStepFormValues($currentStep);

                $stepService = FormObjectFactory::get()->getStepService($formObject);

                if ($currentStep->hasSubsteps()
                    && false === $stepService->lastSubstepWasValidated()
                ) {
                    return;
                }

                $nextStep = $this->service->getNextStep($currentStep);

                if ($nextStep) {
                    /** @var StepDispatchingArguments $arguments */
                    $arguments = GeneralUtility::makeInstance(StepDispatchingArguments::class);

                    $this->beforeSignal()
                        ->withArguments($arguments)
                        ->dispatch();

                    if (false === $arguments->getCancelStepDispatching()) {
                        $this->service->moveForwardToStep($nextStep, $this->redirect());
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getAllowedSignals()
    {
        return [StepDispatchingSignal::class];
    }
}
