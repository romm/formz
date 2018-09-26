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

namespace Romm\Formz\Middleware\Item\Field\Focus;

use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\Definition\Step\Step\Step;
use Romm\Formz\Form\Definition\Step\Step\Substep\SubstepDefinition;
use Romm\Formz\Form\FormObject\FormObjectFactory;
use Romm\Formz\Middleware\Item\OnBeginMiddleware;
use Romm\Formz\Middleware\Item\Step\Service\StepMiddlewareService;
use Romm\Formz\Middleware\Processor\PresetMiddlewareInterface;

/**
 * @todo
 */
class FieldFocusMiddleware extends OnBeginMiddleware implements PresetMiddlewareInterface
{
    const FIELD_FOCUS_ARGUMENT = 'fieldFocus';

    /**
     * @var Step
     */
    protected $step;

    /**
     * @var Field
     */
    protected $field;

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
     * @todo
     */
    protected function process()
    {
        if ($this->getFormObject()->formWasSubmitted()) {
            return;
        }

        $this->step = $this->getCurrentStep();

        if (!$this->step) {
            return;
        }

        if (false === $this->step->hasSubsteps()) {
            return;
        }

        $this->fetchField();

        if (null === $this->field) {
            return;
        }

        $substepDefinition = $this->fetchFieldSubstep();

        if ($substepDefinition) {
            $stepService = FormObjectFactory::get()->getStepService($this->getFormObject());
            $stepService->setCurrentSubstepDefinition($substepDefinition);
        }
    }

    /**
     * @return SubstepDefinition|null
     */
    protected function fetchFieldSubstep()
    {
        return $this->service->findSubstepDefinition(
            $this->step,
            function (SubstepDefinition $substepDefinition) {
                return $substepDefinition->getSubstep()->supportsField($this->field);
            }
        );
    }

    protected function fetchField()
    {
        $request = $this->getRequest();

        if ($request->hasArgument(self::FIELD_FOCUS_ARGUMENT)) {
            $fieldName = $request->getArgument(self::FIELD_FOCUS_ARGUMENT);
            $formDefinition = $this->getFormObject()->getDefinition();

            if ($formDefinition->hasField($fieldName)) {
                $field = $formDefinition->getField($fieldName);

                if ($this->step->supportsField($field)) {
                    $this->field = $field;
                }
            }
        }
    }
}
