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
use Romm\Formz\Service\ViewHelper\Field\FieldViewHelperService;
use Romm\Formz\Service\ViewHelper\Form\FormViewHelperService;
use Romm\Formz\Service\ViewHelper\Slot\SlotViewHelperService;
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
         * Activating the slot service, which will be used all along the
         * rendering of this very field.
         */
        $this->slotService->activate($this->renderingContext);

        /*
         * Calling this here will process every view helper beneath this one,
         * allowing options and slots to be used correctly in the field layout.
         */
        $this->renderChildren();

        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '8.0.0', '<')) {
            $restoreCallback = $this->storeViewDataLegacy();
        }

        $templateArguments = $this->getTemplateArguments();

        $result = $this->renderLayoutView($templateArguments);

        /*
         * Resetting all services data.
         */
        $this->fieldService->removeCurrentField();
        $this->slotService->resetState();

        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '8.0.0', '<')) {
            /** @noinspection PhpUndefinedVariableInspection */
            $restoreCallback($templateArguments);
        }

        return $result;
    }

    /**
     * Will return the associated Fluid view for this field (configured with the
     * `layout` argument).
     *
     * @param array $templateArguments
     * @return string
     */
    protected function renderLayoutView(array $templateArguments)
    {
        $fieldName = $this->arguments['name'];
        $formObject = $this->formService->getFormObject();
        $formConfiguration = $formObject->getDefinition();
        $viewConfiguration = $formConfiguration->getRootConfiguration()->getView();
        $layout = $this->getLayout($viewConfiguration);

        $templateArguments['layout'] = $layout->getLayout();
        $templateArguments['formName'] = $formObject->getName();
        $templateArguments['fieldName'] = $fieldName;
        $templateArguments['fieldId'] = ($templateArguments['fieldId']) ?: StringService::get()->sanitizeString('formz-' . $formObject->getName() . '-' . $fieldName);

        $view = $this->fieldService->getView($layout);

        /*
         * Warning: we need to store the layouts/partials paths before
         * manipulating the rendering context!
         */
        $layoutPaths = $this->getPaths('layout');
        $partialPaths = $this->getPaths('partial');

        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '8.0.0', '<')) {
            $view->setRenderingContext($this->renderingContext);
        } else {
            $renderingContext = $view->getRenderingContext();

            /*
             * Updating the view dependencies: the variable container as well as
             * the controller context must be injected in the view.
             */
            $renderingContext->setViewHelperVariableContainer($this->viewHelperVariableContainer);

            $view->setControllerContext($this->controllerContext);

            $this->viewHelperVariableContainer->setView($view);
        }

        $view->setLayoutRootPaths($layoutPaths);
        $view->setPartialRootPaths($partialPaths);
        $view->assignMultiple($templateArguments);

        return $view->render();
    }

    /**
     * Temporary solution for TYPO3 6.2 to 7.6 that will store the current view
     * variables in a variable, to be able to restore them later.
     *
     * A callback function is returned; it will be called once the field layout
     * view was processed, and will restore all the view data.
     *
     * @return \Closure
     *
     * @deprecated Will be deleted when TYPO3 7.6 is not supported anymore.
     */
    protected function storeViewDataLegacy()
    {
        $originalArguments = [];

        $variableProvider = $this->getVariableProvider();

        foreach (self::$reservedVariablesNames as $key) {
            if ($variableProvider->exists($key)) {
                $originalArguments[$key] = $variableProvider->get($key);
            }
        }

        $viewHelperVariableContainer = $this->renderingContext->getViewHelperVariableContainer();
        $currentView = $viewHelperVariableContainer->getView();

        return function (array $templateArguments) use ($originalArguments, $variableProvider, $viewHelperVariableContainer, $currentView) {
            $viewHelperVariableContainer->setView($currentView);

            /*
             * Cleaning up the variables in the provider: the original
             * values are restored to make the provider like it was before
             * the field rendering started.
             */
            foreach ($variableProvider->getAllIdentifiers() as $identifier) {
                if (array_key_exists($identifier, $templateArguments)) {
                    $variableProvider->remove($identifier);
                }
            }

            foreach ($originalArguments as $key => $value) {
                if ($variableProvider->exists($key)) {
                    $variableProvider->remove($key);
                }

                $variableProvider->add($key, $value);
            }
        };
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
        $formConfiguration = $formObject->getDefinition();

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
     * Merging the arguments with the ones registered with the
     * `OptionViewHelper`.
     *
     * @return array
     */
    protected function getTemplateArguments()
    {
        $templateArguments = $this->arguments['arguments'] ?: [];
        ArrayUtility::mergeRecursiveWithOverrule($templateArguments, $this->fieldService->getFieldOptions());

        return $templateArguments;
    }

    /**
     * This function will determinate the layout/partial root paths that should
     * be given to the standalone view. This must be a merge between the paths
     * given in the TypoScript configuration and the paths of the current view.
     *
     * This way, the user can use the layouts/partials from both the form
     * rendering extension, as well as the ones used by the field layout.
     *
     * Please note that TYPO3 v8+ has this behaviour by default, meaning only
     * the TypoScript configuration paths are needed, however in TYPO3 v7.6- we
     * need to access the root paths, which is *not* granted by Fluid... We are
     * then forced to use reflection, please don't do this at home!
     *
     * @param string $type `partial` or `layout`
     * @return array
     *
     * @deprecated Must be removed when TYPO3 7.6 is not supported anymore!
     */
    protected function getPaths($type)
    {
        $viewConfiguration = $this->formService->getFormObject()->getDefinition()->getRootConfiguration()->getView();

        $paths = $type === 'partial'
            ? $viewConfiguration->getAbsolutePartialRootPaths()
            : $viewConfiguration->getAbsoluteLayoutRootPaths();

        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '8.0.0', '>=')) {
            $templatePaths = $this->renderingContext->getTemplatePaths();

            $currentPaths = $type === 'partial'
                ? $templatePaths->getPartialRootPaths()
                : $templatePaths->getLayoutRootPaths();
        } else {
            $currentView = $this->renderingContext->getViewHelperVariableContainer()->getView();
            $propertyName = $type === 'partial'
                ? 'getPartialRootPaths'
                : 'getLayoutRootPaths';

            $reflectionClass = new \ReflectionClass($currentView);
            $method = $reflectionClass->getMethod($propertyName);
            $method->setAccessible(true);

            $currentPaths = $method->invoke($currentView);
        }

        return array_unique(array_merge($paths, $currentPaths));
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
