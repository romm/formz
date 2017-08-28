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

namespace Romm\Formz\ViewHelpers\Field;

use Romm\Formz\Exceptions\ContextNotFoundException;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\Definition\Step\Step\Step;
use Romm\Formz\Service\ViewHelper\Form\FormViewHelperService;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * @todo
 */
class FocusLinkViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'a';

    /**
     * @var FormViewHelperService
     */
    protected $formService;

    /**
     * Arguments initialization: does also use universal tags.
     */
    public function initializeArguments()
    {
        $this->registerUniversalTagAttributes();

        $this->registerArgument('field', 'string', 'Name of the field.', true);
        $this->registerArgument('step', 'string', 'Identifier of the step, if the field is present of several steps.');
        $this->registerArgument('additionalParams', 'array', 'Additional parameters passed to the URI.', false, []);
    }

    /**
     * @return string
     */
    public function render()
    {
        $this->checkFormContext();
        $this->checkFieldExists();

        $this->tag->addAttribute('href', $this->getUri());
        $this->tag->setContent($this->renderChildren());
        $this->tag->forceClosingTag(true);

        return $this->tag->render();
    }

    /**
     * Builds and returns the URI to the given step, and adds parameters that
     * will allow the system to understand which field to edit.
     *
     * @return string
     */
    protected function getUri()
    {
        $pageUid = null;
        $action = null;
        $controller = null;
        $extensionName = null;

        $arguments = $this->arguments['additionalParams'];

        $step = $this->getStep();

        if ($step) {
            $pageUid = $step->getPageUid();
            $action = $step->getAction();
            $controller = $step->getController();
            $extensionName = $step->getExtension();
        }

        $uriBuilder = $this->controllerContext->getUriBuilder();
        $uri = $uriBuilder->reset();

        if ($pageUid) {
            $uri->setTargetPageUid($pageUid);
        }

        return $uri->uriFor($action, $arguments, $controller, $extensionName);

    }

    /**
     * Checks that the step name given to the view helper exists in the form
     * definition, and returns it.
     *
     * Will also check that the given field name is supported by the step.
     *
     * @return Step
     * @throws \Exception
     */
    protected function getStep()
    {
        $formDefinition = $this->formService->getFormObject()->getDefinition();
        $stepIdentifier = $this->arguments['step'];
        $step = null;

        if ($stepIdentifier) {
            if (false === $formDefinition->getSteps()->hasEntry($stepIdentifier)) {
                throw new \Exception('@todo : the step "' . $stepIdentifier . '" does not exists.'); // @todo
            }

            $step = $formDefinition->getSteps()->getEntry($stepIdentifier);
            $field = $this->getField();

            if (false === $step->supportsField($field)) {
                throw new \Exception('@todo : the step "' . $step->getIdentifier() . '" does not support the field "' . $field->getName() . '".'); // @todo
            }
        }

        return $step;
    }

    /**
     * @throws ContextNotFoundException
     */
    protected function checkFormContext()
    {
        if (false === $this->formService->formContextExists()) {
            throw new ContextNotFoundException('form context not found'); // @todo
        }
    }

    /**
     * Checks that the given field name exists in the form definition.
     */
    protected function checkFieldExists()
    {
        $formDefinition = $this->formService->getFormObject()->getDefinition();
        $fieldName = $this->arguments['field'];

        if (false === $formDefinition->hasField($fieldName)) {
            throw new \Exception('field not found : ' . $fieldName); // @todo
        }
    }

    /**
     * Returns the field instance from the argument given to the view helper.
     *
     * @return Field
     */
    protected function getField()
    {
        return $this->formService
            ->getFormObject()
            ->getDefinition()
            ->getField($this->arguments['field']);
    }

    /**
     * @param FormViewHelperService $service
     */
    public function injectFormService(FormViewHelperService $service)
    {
        $this->formService = $service;
    }
}
