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
use Romm\Formz\Form\FormObject\FormObjectFactory;
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
            $stepService = FormObjectFactory::get()->getStepService($formObject);

            if (false === $stepService->lastSubstepWasValidated()) {
                return;
            }

            $this->service->redirectToNextStep($this->getCurrentStep(), $this->redirect());
        }
    }
}
