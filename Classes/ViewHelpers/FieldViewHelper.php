<?php
/*
 * 2017 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Formz project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\ViewHelpers;

use Romm\Formz\Configuration\View\Layouts\Layout;
use Romm\Formz\Configuration\View\View;
use Romm\Formz\Core\Core;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Exceptions\InvalidArgumentTypeException;
use Romm\Formz\Exceptions\InvalidArgumentValueException;
use Romm\Formz\Service\StringService;
use Romm\Formz\ViewHelpers\Service\FieldService;
use Romm\Formz\ViewHelpers\Service\FormService;
use Romm\Formz\ViewHelpers\Service\SectionService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Utility\ArrayUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * This view helper is used to automatize the rendering of a field layout. It
 * will use the TypoScript properties at the path `config.tx_formz.view.layout`.
 *
 * You need to indicate the name of the field which will be rendered, and the
 * name of the layout which should be used (it must be present in the TypoScript
 * configuration).
 *
 * Example of layout: `bootstrap.3-cols`. You may indicate only the group, then
 * the name of the layout will be set to `default` (if you use the layout group
 * `bootstrap`, the layout `default` will be used, only if it does exist of
 * course).
 */
class FieldViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @var array
     */
    public static $reservedVariablesNames = ['layout', 'formName', 'fieldName', 'fieldId'];

    /**
     * @var FormService
     */
    protected $formService;

    /**
     * @var FieldService
     */
    protected $fieldService;

    /**
     * @var SectionService
     */
    protected $sectionService;

    /**
     * @var array
     */
    protected $originalArguments = [];

    /**
     * @inheritdoc
     */
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Name of the field which should be rendered.', true);
        $this->registerArgument('layout', 'string', 'Path of the TypoScript layout which will be used.', true);
        $this->registerArgument('arguments', 'array', 'Arbitrary arguments which will be sent to the field template.', false, []);
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        $viewHelperVariableContainer = $this->renderingContext->getViewHelperVariableContainer();

        /*
         * First, we check if this view helper is called from within the
         * `FormViewHelper`, because it would not make sense anywhere else.
         */
        $this->formService->checkIsInsideFormViewHelper();

        /*
         * Then, we inject the wanted field in the `FieldService` so we can know
         * later which field we're working with.
         */
        $this->injectFieldInService($this->arguments['name']);

        /*
         * Calling this here will process every view helper beneath this one,
         * allowing options and sections to be used correctly in the field
         * layout.
         */
        $this->renderChildren();

        /*
         * We need to store original arguments declared for the current view
         * context, because we may override them during the rendering of this
         * view helper.
         */
        $this->storeOriginalArguments();

        /*
         * We merge the arguments with the ones registered with the
         * `OptionViewHelper`.
         */
        $templateArguments = $this->arguments['arguments'] ?: [];
        $templateArguments = ArrayUtility::arrayMergeRecursiveOverrule($templateArguments, $this->fieldService->getFieldOptions());

        $currentView = $viewHelperVariableContainer->getView();

        $result = $this->renderLayoutView($templateArguments);

        /*
         * Resetting all services data.
         */
        $this->fieldService->removeCurrentField();
        $this->fieldService->resetFieldOptions();
        $this->sectionService->resetSectionClosures();

        $viewHelperVariableContainer->setView($currentView);
        $this->restoreOriginalArguments($templateArguments);

        return $result;
    }

    /**
     * Will render the associated Fluid view for this field (configured with the
     * `layout` argument).
     *
     * @param array $templateArguments
     * @return string
     */
    protected function renderLayoutView(array $templateArguments)
    {
        $fieldName = $this->arguments['name'];
        $formObject = $this->formService->getFormObject();
        $formConfiguration = $formObject->getConfiguration();
        $viewConfiguration = $formConfiguration->getFormzConfiguration()->getView();
        $layout = $this->getLayout($viewConfiguration);

        $templateArguments['layout'] = $layout->getLayout();
        $templateArguments['formName'] = $formObject->getName();
        $templateArguments['fieldName'] = $fieldName;
        $templateArguments['fieldId'] = ($templateArguments['fieldId']) ?: StringService::get()->sanitizeString('formz-' . $formObject->getName() . '-' . $fieldName);

        /** @var StandaloneView $view */
        $view = Core::instantiate(StandaloneView::class);
        $view->setTemplatePathAndFilename($layout->getTemplateFile());
        $view->setLayoutRootPaths($viewConfiguration->getLayoutRootPaths());
        $view->setPartialRootPaths($viewConfiguration->getPartialRootPaths());

        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '8.0.0', '<')) {
            $view->setRenderingContext($this->renderingContext);
        } else {
            $variableProvider = $this->getVariableProvider();
            foreach ($templateArguments as $key => $value) {
                if ($variableProvider->exists($key)) {
                    $variableProvider->remove($key);
                }

                $variableProvider->add($key, $value);
            }
        }

        $view->assignMultiple($templateArguments);

        return $view->render();
    }

    /**
     * Will check that the given field exists in the current form definition and
     * inject it in the `FieldService` as `currentField`.
     *
     * Throws an error if the field is not found or incorrect.
     *
     * @param string $fieldName
     * @throws EntryNotFoundException
     * @throws InvalidArgumentTypeException
     */
    protected function injectFieldInService($fieldName)
    {
        $formObject = $this->formService->getFormObject();
        $formConfiguration = $formObject->getConfiguration();

        if (false === is_string($fieldName)) {
            throw new InvalidArgumentTypeException(
                'The argument "name" of the view helper "' . __CLASS__ . '" must be a string.',
                1465243479
            );
        } elseif (false === $formConfiguration->hasField($fieldName)) {
            throw new EntryNotFoundException(
                'The form "' . $formObject->getClassName() . '" does not have an accessible property "' . $fieldName . '". Please be sure this property exists, and it has a proper getter to access its value.',
                1465243619);
        }

        $this->fieldService->setCurrentField($formConfiguration->getField($fieldName));
    }

    /**
     * Returns the layout instance used by this field.
     *
     * @param View $viewConfiguration
     * @return Layout
     * @throws EntryNotFoundException
     * @throws InvalidArgumentTypeException
     * @throws InvalidArgumentValueException
     */
    protected function getLayout(View $viewConfiguration)
    {
        $layout = $this->arguments['layout'];

        if (false === is_string($layout)) {
            throw new InvalidArgumentTypeException(
                'The argument "layout" must be a string (' . gettype($layout) . ' given).',
                1485786193
            );
        }

        list($layoutName, $templateName) = GeneralUtility::trimExplode('.', $layout);
        if (false === is_string($templateName)) {
            $templateName = 'default';
        }

        if (empty($layoutName)) {
            throw new InvalidArgumentValueException(
                'The layout name cannot be empty, please fill with a value.',
                1485786285
            );
        }

        if (false === $viewConfiguration->hasLayout($layoutName)) {
            throw new EntryNotFoundException(
                'The layout "' . $layout . '" could not be found. Please check your TypoScript configuration.',
                1465243586
            );
        }

        if (false === $viewConfiguration->getLayout($layoutName)->hasItem($templateName)) {
            throw new EntryNotFoundException(
                'The layout "' . $layout . '" does not have an item "' . $templateName . '".',
                1485867803
            );
        }

        return $viewConfiguration->getLayout($layoutName)->getItem($templateName);
    }

    /**
     * Stores some arguments which may already have been initialized, and could
     * be overridden in the local scope.
     */
    protected function storeOriginalArguments()
    {
        $this->originalArguments = [];
        $variableProvider = $this->getVariableProvider();

        foreach (self::$reservedVariablesNames as $key) {
            if ($variableProvider->exists($key)) {
                $this->originalArguments[$key] = $variableProvider->get($key);
            }
        }
    }

    /**
     * Will restore original arguments in the template variable container.
     *
     * @param array $templateArguments
     */
    protected function restoreOriginalArguments(array $templateArguments)
    {
        $variableProvider = $this->getVariableProvider();

        foreach ($variableProvider->getAllIdentifiers() as $identifier) {
            if (array_key_exists($identifier, $templateArguments)) {
                $variableProvider->remove($identifier);
            }
        }

        foreach ($this->originalArguments as $key => $value) {
            if ($variableProvider->exists($key)) {
                $variableProvider->remove($key);
            }

            $variableProvider->add($key, $value);
        }
    }

    /**
     * @param FormService $service
     */
    public function injectFormService(FormService $service)
    {
        $this->formService = $service;
    }

    /**
     * @param FieldService $service
     */
    public function injectFieldService(FieldService $service)
    {
        $this->fieldService = $service;
    }

    /**
     * @param SectionService $sectionService
     */
    public function injectSectionService(SectionService $sectionService)
    {
        $this->sectionService = $sectionService;
    }
}
