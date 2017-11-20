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

use Romm\Formz\Middleware\Argument\Arguments;
use Romm\Formz\Middleware\Item\AbstractMiddleware;
use Romm\Formz\Middleware\Item\FormValidation\FormValidationSignal;
use Romm\Formz\Middleware\Item\Step\Service\StepMiddlewareService;
use Romm\Formz\Middleware\Scope\FieldValidationScope;
use Romm\Formz\Middleware\Scope\ReadScope;
use Romm\Formz\Middleware\Signal\Before;
use Romm\Formz\ViewHelpers\Step\PreviousLinkViewHelper;

/**
 * @todo
 */
class PreviousStepMiddleware extends AbstractMiddleware implements Before, FormValidationSignal
{
    /**
     * @var int
     */
    protected $priority = self::PRIORITY_STEP + 100;

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
    public function before(Arguments $arguments)
    {
        $formObject = $this->getFormObject();
        $this->service->reset($formObject, $this->getRequest());

        if (false === $formObject->getDefinition()->hasSteps()) {
            return;
        }

        if (!$this->getRequest()->hasArgument(PreviousLinkViewHelper::PREVIOUS_LINK_PARAMETER)) {
            return;
        }

        $currentStep = $this->getCurrentStep();

        if ($currentStep) {
            $stepDefinition = $this->service->getStepDefinition($currentStep);

            if ($stepDefinition->hasPreviousDefinition()) {
                $this->service->redirectToStep($stepDefinition->getPreviousDefinition()->getStep(), $this->redirect());
            }
        }
    }
}
