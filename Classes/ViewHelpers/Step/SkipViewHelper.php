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

use Exception;
use Romm\Formz\Exceptions\ContextNotFoundException;
use Romm\Formz\Form\Definition\Step\Step\Step;
use Romm\Formz\Service\ViewHelper\Form\FormViewHelperService;
use Romm\Formz\ViewHelpers\FormViewHelper;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;

class SkipViewHelper extends AbstractFormFieldViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'input';

    /**
     * @var FormViewHelperService
     */
    protected $formService;

    /**
     * @inheritDoc
     */
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerUniversalTagAttributes();

        $this->registerArgument('substep', 'string', 'Identifier of the optional substep to be skipped.');
    }

    /**
     * @return string
     */
    public function render(): string
    {
        /*
        * First, we check if this view helper is called from within the
        * `FormViewHelper`, because it would not make sense anywhere else.
        */
        if (false === $this->formService->formContextExists()) {
            throw ContextNotFoundException::skipViewHelperFormContextNotFound();
        }

        $formObject = $this->formService->getFormObject();
        $formDefinition = $formObject->getDefinition();

        if (false === $formDefinition->hasSteps()) {
            throw new Exception('No stop for this form.');
        }

        /** @var Request $request */
        $request = $this->controllerContext->getRequest();
        $currentStep = $formObject->fetchCurrentStep($request)->getCurrentStep();

        if ($substep = $this->arguments['substep'] ?? null) {
            if (false === $currentStep->hasSubsteps()) {
                throw new Exception('No substeps for this form.');
            }

            $substeps = $currentStep->getSubsteps();

            if (false === $substeps->hasEntry($substep)) {
                throw new Exception("No substep `$substep` for this form.");
            }

            $this->tag->addAttribute('fz-substep', $substep);
        }

        $this->tag->addAttribute('type', 'submit');
        $this->tag->addAttribute('value', $this->getValueAttribute());
        $this->tag->addAttribute('formaction', $this->formAction($currentStep));

        return $this->tag->render();
    }

    private function formAction(Step $step): string
    {
        $formArguments = $this->viewHelperVariableContainer->get(FormViewHelper::class, 'arguments');

        $pageUid = (int)$formArguments['pageUid'] > 0 ? (int)$formArguments['pageUid'] : null;

        /** @var UriBuilder $uriBuilder */
        $uriBuilder = $this->renderingContext->getControllerContext()->getUriBuilder();

        $arguments = (array)$formArguments['arguments'];

        $arguments['skip'] = true;
        $arguments['step'] = $step->getIdentifier();

        $substep = $this->arguments['substep'] ?? null;

        if ($substep) {
            $arguments['skipSubsteps'] = $substep;
        }

        return $uriBuilder
            ->reset()
            ->setTargetPageUid($pageUid)
            ->setTargetPageType($formArguments['pageType'])
            ->setNoCache($formArguments['noCache'])
            ->setUseCacheHash(!$formArguments['noCacheHash'])
            ->setSection($formArguments['section'])
            ->setCreateAbsoluteUri($formArguments['absolute'])
            ->setArguments((array)$formArguments['additionalParams'])
            ->setAddQueryString($formArguments['addQueryString'])
            ->setAddQueryStringMethod($formArguments['addQueryStringMethod'])
            ->setArgumentsToBeExcludedFromQueryString((array)$formArguments['argumentsToBeExcludedFromQueryString'])
            ->setFormat($formArguments['format'])
            ->uriFor(
                $formArguments['action'],
                $arguments,
                $formArguments['controller'],
                $formArguments['extensionName'],
                $formArguments['pluginName']
            );
    }

    /**
     * @param FormViewHelperService $service
     */
    public function injectFormService(FormViewHelperService $service)
    {
        $this->formService = $service;
    }
}
