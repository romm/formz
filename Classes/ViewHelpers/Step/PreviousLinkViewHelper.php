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

namespace Romm\Formz\ViewHelpers\Step;

use Romm\Formz\Middleware\Item\Step\Service\StepMiddlewareService;
use Romm\Formz\Service\ViewHelper\Form\FormViewHelperService;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;

class PreviousLinkViewHelper extends AbstractFormFieldViewHelper
{
    const PREVIOUS_LINK_PARAMETER = 'substepsPrevious';

    /**
     * @var string
     */
    protected $tagName = 'input';

    /**
     * @var FormViewHelperService
     */
    protected $formService;

    /**
     * @var ControllerContext
     */
    protected $controllerContext;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
    }

    public function render()
    {
        if (!$this->controllerContext) {
            $this->controllerContext = $this->renderingContext->getControllerContext();
        }

        /*
         * First, we check if this view helper is called from within the
         * `FormViewHelper`, because it would not make sense anywhere else.
         */
        if (false === $this->formService->formContextExists()) {
            // @todo
//            throw ContextNotFoundException::substepViewHelperFormContextNotFound();
        }

        $formObject = $this->formService->getFormObject();
        $formDefinition = $formObject->getDefinition();

        if (false === $formDefinition->hasSteps()) {
            throw new \Exception('todo'); // @todo
        }

        /** @var Request $request */
        $request = $this->controllerContext->getRequest();
        $currentStep = $formObject->fetchCurrentStep($request)->getCurrentStep();
        $stepDefinition = StepMiddlewareService::get()->getStepDefinition($currentStep);

        // @todo handle previous steps depth
        if (!$stepDefinition->hasPreviousDefinition()
            && !$currentStep->hasSubsteps()
        ) {
            return null;
        }

        $this->tag->addAttribute('type', 'submit');
        $this->tag->addAttribute('value', $this->getValueAttribute());
        $this->tag->addAttribute('name', $this->prefixFieldName(PreviousLinkViewHelper::PREVIOUS_LINK_PARAMETER));
        $this->tag->addAttribute('fz-previous-link', true);

        return $this->tag->render();
    }

    /**
     * @param FormViewHelperService $service
     */
    public function injectFormService(FormViewHelperService $service)
    {
        $this->formService = $service;
    }
}
