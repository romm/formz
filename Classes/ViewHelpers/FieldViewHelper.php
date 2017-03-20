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

namespace Romm\Formz\ViewHelpers;

use Romm\Formz\Configuration\View\Layouts\Layout;
use Romm\Formz\Configuration\View\View;
use Romm\Formz\Exceptions\ContextNotFoundException;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Exceptions\InvalidArgumentTypeException;
use Romm\Formz\Exceptions\InvalidArgumentValueException;
use Romm\Formz\Exceptions\PropertyNotAccessibleException;
use Romm\Formz\Service\StringService;
use Romm\Formz\Service\ViewHelper\FieldViewHelperService;
use Romm\Formz\Service\ViewHelper\FormViewHelperService;
use Romm\Formz\Service\ViewHelper\SlotViewHelperService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

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
     * @var FormViewHelperService
     */
    protected $formService;

    /**
     * @var FieldViewHelperService
     */
    protected $fieldService;

    /**
     * @var SlotViewHelperService
     */
    protected $slotService;

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
        if (false === $this->formService->formContextExists()) {
            throw ContextNotFoundException::fieldViewHelperFormContextNotFound();
        }

        /*
         * Then, we inject the wanted field in the `FieldService` so we can know
         * later which field we're working with.
         */
        $this->injectFieldInService($this->arguments['name']);

        /*
         * Calling this here will process every view helper beneath this one,
         * allowing options and slots to be used correctly in the field layout.
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
        ArrayUtility::mergeRecursiveWithOverrule($templateArguments, $this->fieldService->getFieldOptions());

        $currentView = $viewHelperVariableContainer->getView();

        $result = $this->renderLayoutView($templateArguments);

        /*
         * Resetting all services data.
         */
        $this->fieldService->resetState();
        $this->slotService->resetState();

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
        $viewConfiguration = $formConfiguration->getRootConfiguration()->getView();
        $layout = $this->getLayout($viewConfiguration);

        $templateArguments['layout'] = $layout->getLayout();
        $templateArguments['formName'] = $formObject->getName();
        $templateArguments['fieldName'] = $fieldName;
        $templateArguments['fieldId'] = ($templateArguments['fieldId']) ?: StringService::get()->sanitizeString('formz-' . $formObject->getName() . '-' . $fieldName);

        $view = $this->fieldService->getView();

        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '8.0.0', '<')) {
            $view->setRenderingContext($this->renderingContext);
        } else {
            $view->setControllerContext($this->controllerContext);

            $variableProvider = $this->getVariableProvider();

            foreach ($templateArguments as $key => $value) {
                if ($variableProvider->exists($key)) {
                    $variableProvider->remove($key);
                }

                $variableProvider->add($key, $value);
            }
        }

        $view->setTemplatePathAndFilename($layout->getTemplateFile());
        $view->setLayoutRootPaths($viewConfiguration->getAbsoluteLayoutRootPaths());
        $view->setPartialRootPaths($viewConfiguration->getAbsolutePartialRootPaths());
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
     * @throws InvalidArgumentTypeException
     * @throws PropertyNotAccessibleException
     */
    protected function injectFieldInService($fieldName)
    {
        $formObject = $this->formService->getFormObject();
        $formConfiguration = $formObject->getConfiguration();

        if (false === is_string($fieldName)) {
            throw InvalidArgumentTypeException::fieldViewHelperInvalidTypeNameArgument();
        } elseif (false === $formConfiguration->hasField($fieldName)) {
            throw PropertyNotAccessibleException::fieldViewHelperFieldNotAccessibleInForm($formObject, $fieldName);
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
            throw InvalidArgumentTypeException::invalidTypeNameArgumentFieldViewHelper($layout);
        }

        list($layoutName, $templateName) = GeneralUtility::trimExplode('.', $layout);

        if (empty($templateName)) {
            $templateName = 'default';
        }

        if (empty($layoutName)) {
            throw InvalidArgumentValueException::fieldViewHelperEmptyLayout();
        }

        if (false === $viewConfiguration->hasLayout($layoutName)) {
            throw EntryNotFoundException::fieldViewHelperLayoutNotFound($layout);
        }

        if (false === $viewConfiguration->getLayout($layoutName)->hasItem($templateName)) {
            throw EntryNotFoundException::fieldViewHelperLayoutItemNotFound($layout, $templateName);
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
     * @param FormViewHelperService $service
     */
    public function injectFormService(FormViewHelperService $service)
    {
        $this->formService = $service;
    }

    /**
     * @param FieldViewHelperService $service
     */
    public function injectFieldService(FieldViewHelperService $service)
    {
        $this->fieldService = $service;
    }

    /**
     * @param SlotViewHelperService $slotService
     */
    public function injectSlotService(SlotViewHelperService $slotService)
    {
        $this->slotService = $slotService;
    }
}
