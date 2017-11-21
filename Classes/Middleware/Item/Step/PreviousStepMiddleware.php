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

        if (!$this->getRequest()->hasArgument(PreviousLinkViewHelper::PREVIOUS_LINK_PARAMETER)) {
            return;
        }

        $currentStep = $this->getCurrentStep();

        if ($currentStep) {
            $stepDefinition = $this->service->getStepDefinition($currentStep);

            if ($currentStep->hasSubsteps()) {
                $stepService = FormObjectFactory::get()->getStepService($this->getFormObject());
                $substepsLevel = $stepService->getSubstepsLevel();

                if ($substepsLevel > 1) {
                    $substepDefinition = $currentStep->getSubsteps()->getFirstSubstepDefinition();

                    while ($substepDefinition) {
                        $nextSubstepDefinition = $this->service->getNextSubstepDefinition($substepDefinition);

                        if (!$nextSubstepDefinition
                            || $nextSubstepDefinition->getLevel() >= $substepsLevel - 1
                        ) {
                            break;
                        }

                        $substepDefinition = $nextSubstepDefinition;
                    }

                    $stepService->setCurrentSubstepDefinition($substepDefinition);
                    $stepService->setCurrentStep($currentStep);
                    $stepService->setSubstepsLevel($substepDefinition->getLevel());
//                    \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($substepDefinition, __CLASS__ . ':' . __LINE__ . ' $substepDefinition');
//                    \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($substepDefinition->getLevel(), __CLASS__ . ':' . __LINE__ . ' $substepDefinition->getLevel()');
//                    \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($currentStep, __CLASS__ . ':' . __LINE__ . ' $currentStep');
//                    \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($stepService, __CLASS__ . ':' . __LINE__ . ' $stepService');
//                    die();

                    return;
                }
            }


            if ($stepDefinition->hasPreviousDefinition()) {
                $this->service->redirectToStep($stepDefinition->getPreviousDefinition()->getStep(), $this->redirect());
            }
        }
    }
}
