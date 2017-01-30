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
use Romm\Formz\Service\StringService;
use Romm\Formz\ViewHelpers\Service\FieldService;
use Romm\Formz\ViewHelpers\Service\FormService;
use Romm\Formz\ViewHelpers\Service\SectionService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ArrayUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
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
     * Unique instance of view, stored to save some performance.
     *
     * @var StandaloneView
     */
    protected static $view;

    /**
     * @inheritdoc
     */
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Name of the field which should be rendered.', true);
        $this->registerArgument('layout', 'string', 'Path of the TypoScript layout which will be used.', true);
        $this->registerArgument('arguments', 'array', 'Arbitrary arguments which will be sent to the field template.', false);
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        $this->formService->checkIsInsideFormViewHelper();

        $formObject = $this->formService->getFormObject();

        $formConfiguration = $formObject->getConfiguration();

        $viewConfiguration = $formConfiguration->getFormzConfiguration()->getView();
        $fieldName = $this->arguments['name'];

        if (false === is_string($this->arguments['name'])) {
            throw new \Exception('The argument "name" of the view helper "' . __CLASS__ . '" must be a string.', 1465243479);
        } elseif (false === $formConfiguration->hasField($fieldName)) {
            throw new \Exception('The form "' . $formObject->getClassName() . '" does not have an accessible property "' . $fieldName . '". Please be sure this property exists, and it has a proper getter to access its value.', 1465243619);
        }

        $this->fieldService->setCurrentField($formConfiguration->getField($fieldName));

        $closure = $this->buildRenderChildrenClosure();
        $closure();

        $layout = self::getLayout($this->arguments, $viewConfiguration);

        /** @var StandaloneView $view */
        $view = self::$view = (null === self::$view)
            ? Core::instantiate(StandaloneView::class)
            : self::$view;

        $templateArguments = is_array($this->arguments['arguments'])
            ? $this->arguments['arguments']
            : [];
        $templateArguments = ArrayUtility::arrayMergeRecursiveOverrule($templateArguments, $this->fieldService->getFieldOptions());

        /*
         * Keeping a trace of potential original arguments which will be
         * replaced in the section, to restore them at the end of the view
         * helper.
         */
        $originalArguments = self::getOriginalArguments($this->renderingContext);

        $templateArguments['layout'] = $layout->getLayout();
        $templateArguments['formName'] = $formObject->getName();
        $templateArguments['fieldName'] = $fieldName;
        $templateArguments['fieldId'] = (true === isset($templateArguments['fieldId']))
            ? $templateArguments['fieldId']
            : StringService::get()->sanitizeString('formz-' . $formObject->getName() . '-' . $fieldName);

        $currentView = $this->renderingContext
            ->getViewHelperVariableContainer()
            ->getView();

        $view->setTemplatePathAndFilename($layout->getTemplateFile());
        $view->setLayoutRootPaths($viewConfiguration->getLayoutRootPaths());
        $view->setPartialRootPaths($viewConfiguration->getPartialRootPaths());
        $view->setRenderingContext($this->renderingContext);
        $view->assignMultiple($templateArguments);

        $result = $view->render();

        $this->renderingContext
            ->getViewHelperVariableContainer()
            ->setView($currentView);

        $this->fieldService
            ->removeCurrentField()
            ->resetFieldOptions();

        $this->sectionService->resetSectionClosures();

        self::restoreOriginalArguments($this->renderingContext, $templateArguments, $originalArguments);

        return $result;
    }

    /**
     * Returns the layout instance used by this field.
     *
     * @param array $arguments
     * @param View  $viewConfiguration
     * @return Layout
     * @throws \Exception
     */
    protected static function getLayout(array $arguments, View $viewConfiguration)
    {
        $layoutFound = true;
        list($layoutName, $templateName) = GeneralUtility::trimExplode('.', $arguments['layout']);
        if (false === is_string($templateName)) {
            $templateName = 'default';
        }

        if (false === is_string($layoutName)) {
            $layoutFound = false;
        } elseif (false === $viewConfiguration->hasLayout($layoutName)) {
            $layoutFound = false;
        } elseif (false === $viewConfiguration->getLayout($layoutName)->hasItem($templateName)) {
            $layoutFound = false;
        }

        if (false === $layoutFound) {
            throw new \Exception('The layout "' . $arguments['layout'] . '" could not be found. Please check your TypoScript configuration.', 1465243586);
        }

        return $viewConfiguration->getLayout($layoutName)->getItem($templateName);
    }

    /**
     * Returns the value of the current variable in the variable container at
     * the index `$key`, or null if it is not found.
     *
     * @param RenderingContextInterface $renderingContext
     * @param string                    $key
     * @return mixed|null
     */
    protected static function getTemplateVariableContainerValue(RenderingContextInterface $renderingContext, $key)
    {
        $templateVariableContainer = $renderingContext->getTemplateVariableContainer();

        return ($templateVariableContainer->exists($key))
            ? $templateVariableContainer->get($key)
            : null;
    }

    /**
     * Returns some arguments which may already have been initialized.
     *
     * @param RenderingContextInterface $renderingContext
     * @return array
     */
    protected static function getOriginalArguments(RenderingContextInterface $renderingContext)
    {
        return [
            'layout'    => self::getTemplateVariableContainerValue($renderingContext, 'layout'),
            'formName'  => self::getTemplateVariableContainerValue($renderingContext, 'formName'),
            'fieldName' => self::getTemplateVariableContainerValue($renderingContext, 'fieldName'),
            'fieldId'   => self::getTemplateVariableContainerValue($renderingContext, 'fieldId')
        ];
    }

    /**
     * Will restore original arguments in the template variable container.
     *
     * @param RenderingContextInterface $renderingContext
     * @param array                     $templateArguments
     * @param array                     $originalArguments
     */
    protected static function restoreOriginalArguments(RenderingContextInterface $renderingContext, array $templateArguments, array $originalArguments)
    {
        $templateVariableContainer = $renderingContext->getTemplateVariableContainer();

        $identifiers = $templateVariableContainer->getAllIdentifiers();
        foreach ($identifiers as $identifier) {
            if (array_key_exists($identifier, $templateArguments)) {
                $templateVariableContainer->remove($identifier);
            }
        }

        foreach ($originalArguments as $key => $value) {
            if (null !== $value) {
                if ($templateVariableContainer->exists($key)) {
                    $templateVariableContainer->remove($key);
                }

                $templateVariableContainer->add($key, $value);
            }
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
