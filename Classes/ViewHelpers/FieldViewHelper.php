<?php
/*
 * 2016 Romain CANON <romain.hydrocanon@gmail.com>
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

use Romm\Formz\AssetHandler\Html\DataAttributesAssetHandler;
use Romm\Formz\Configuration\View\Layouts\Layout;
use Romm\Formz\Configuration\View\View;
use Romm\Formz\Core\Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ArrayUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;
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
class FieldViewHelper extends AbstractViewHelper implements CompilableInterface
{
    const FIELD_INSTANCE = 'FieldInstance';

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
        $this->checkIsInsideFormViewHelper();

        return self::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
    }

    /**
     * @inheritdoc
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        /** @var FormViewHelper $form */
        $form = FormViewHelper::getVariable(FormViewHelper::FORM_VIEW_HELPER);
        $viewConfiguration = $form->getFormzConfiguration()->getView();
        $formConfiguration = $form->getFormObject()->getConfiguration();
        $fieldName = $arguments['name'];

        if (false === is_string($arguments['name'])) {
            throw new \Exception('The argument "name" of the view helper "' . __CLASS__ . '" must be a string.', 1465243479);
        } elseif (false === $formConfiguration->hasField($fieldName)) {
            throw new \Exception('The form "' . $form->getFormObject()->getClassName() . '" does not have an accessible property "' . $fieldName . '". Please be sure this property exists, and it has a proper getter to access its value.', 1465243619);
        }

        $renderingContext->getViewHelperVariableContainer()->addOrUpdate(__CLASS__, self::FIELD_INSTANCE, $formConfiguration->getField($fieldName));

        $renderChildrenClosure();

        $layout = self::getLayout($arguments, $viewConfiguration);

        /** @var StandaloneView $view */
        $view = self::$view = (null === self::$view)
            ? Core::get()->getObjectManager()->get(StandaloneView::class)
            : self::$view;

        $templateArguments = is_array($arguments['arguments'])
            ? $arguments['arguments']
            : [];
        $templateArguments = ArrayUtility::arrayMergeRecursiveOverrule($templateArguments, OptionViewHelper::getOption());

        /*
         * Keeping a trace of potential original arguments which will be
         * replaced in the section, to restore them at the end of the view
         * helper.
         */
        $originalArguments = self::getOriginalArguments($renderingContext);

        $templateArguments['layout'] = $layout->getLayout();
        $templateArguments['formName'] = $form->getFormObject()->getName();
        $templateArguments['fieldName'] = $fieldName;
        $templateArguments['fieldId'] = (true === isset($templateArguments['fieldId']))
            ? $templateArguments['fieldId']
            : DataAttributesAssetHandler::getFieldCleanName('formz-' . $form->getFormObject()->getName() . '-' . $fieldName);

        $currentView = $renderingContext->getViewHelperVariableContainer()->getView();

        $view->setTemplatePathAndFilename($layout->getTemplateFile());
        $view->setLayoutRootPaths($viewConfiguration->getLayoutRootPaths());
        $view->setPartialRootPaths($viewConfiguration->getPartialRootPaths());
        $view->setRenderingContext($renderingContext);
        $view->assignMultiple($templateArguments);

        $result = $view->render();

        $renderingContext->getViewHelperVariableContainer()->setView($currentView);

        $renderingContext->getViewHelperVariableContainer()->remove(__CLASS__, self::FIELD_INSTANCE);
        SectionViewHelper::resetSectionClosures();
        OptionViewHelper::resetOptions();

        self::restoreOriginalArguments($renderingContext, $templateArguments, $originalArguments);

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
}
