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

use Romm\Formz\Exceptions\ContextNotFoundException;
use Romm\Formz\Form\Definition\Step\Step\Substep\Substep;
use Romm\Formz\Service\ViewHelper\Form\FormViewHelperService;
use Romm\Formz\ViewHelpers\AbstractViewHelper;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Mvc\Web\Request;

class SubstepViewHelper extends AbstractViewHelper
{
    /**
     * @var FormViewHelperService
     */
    protected $formService;

    /**
     * @var Error[]
     */
    protected $errors = [];

    /**
     * @inheritdoc
     */
    public function initializeArguments()
    {
        $this->registerArgument('identifier', 'string', 'Identifier of the substep.', true);
    }

    public function render()
    {
        /*
         * First, we check if this view helper is called from within the
         * `FormViewHelper`, because it would not make sense anywhere else.
         */
        if (false === $this->formService->formContextExists()) {
            throw ContextNotFoundException::substepViewHelperFormContextNotFound();
        }

        $formObject = $this->formService->getFormObject();
        $formDefinition = $formObject->getDefinition();

        if (false === $formDefinition->hasSteps()) {
            throw new \Exception('todo'); // @todo
        }

        /** @var Request $request */
        $request = $this->controllerContext->getRequest();
        $currentStep = $this->formService->getFormObject()->fetchCurrentStep($request)->getCurrentStep();

        if (false === $currentStep->hasSubsteps()) {
            throw new \Exception('todo'); // @todo
        }

        $substeps = $currentStep->getSubsteps();
        $substepIdentifier = $this->arguments['identifier'];

        if (false === $substeps->hasEntry($substepIdentifier)) {
            throw new \Exception('todo'); // @todo
        }

        $currentFields = $this->formService->getCurrentFormFieldNames($this->viewHelperVariableContainer);

        $content = $this->renderChildren();

        $this->checkSubstepSupportedFields($currentFields, $substeps->getEntry($substepIdentifier));

        return <<<XML
<div fz-substep="{$this->arguments['identifier']}">
    $content
</div>
XML;
    }

    /**
     * @todo
     *
     * @param array   $currentFieldsBegin
     * @param Substep $substep
     */
    protected function checkSubstepSupportedFields(array $currentFieldsBegin, Substep $substep)
    {
        $currentFieldsEnd = $this->formService->getCurrentFormFieldNames($this->viewHelperVariableContainer);
        $currentFields = array_diff($currentFieldsEnd, $currentFieldsBegin);

        $formDefinition = $this->formService->getFormObject()->getDefinition();

        foreach ($currentFields as $fieldName) {
            if (false === $formDefinition->hasField($fieldName)) {
                continue;
            }

            $field = $formDefinition->getField($fieldName);

            if (false === $substep->supportsField($field)) {
                $error = new Error('todo ' . $fieldName . ' (' . $substep->getIdentifier() . ')', 123); // @todo

                $this->errors[] = $error;
                $this->formService->getResult()->addError($error);
            }
        }
    }

    /**
     * @param FormViewHelperService $service
     */
    public function injectFormService(FormViewHelperService $service)
    {
        $this->formService = $service;
    }
}
