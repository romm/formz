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

use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\AssetHandler\Connector\AssetHandlerConnectorManager;
use Romm\Formz\AssetHandler\Html\DataAttributesAssetHandler;
use Romm\Formz\Behaviours\BehavioursManager;
use Romm\Formz\Core\Core;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObjectFactory;
use Romm\Formz\Service\ContextService;
use Romm\Formz\Service\ExtensionService;
use Romm\Formz\Service\StringService;
use Romm\Formz\Service\TimeTrackerService;
use Romm\Formz\Validation\Validator\Form\DefaultFormValidator;
use Romm\Formz\ViewHelpers\Service\FormService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * This view helper overrides the default one from Extbase, to include
 * everything the extension needs to work properly.
 *
 * The only difference in Fluid is that the attribute "name" becomes mandatory,
 * and must be the exact same name as the form parameter in the controller
 * action called when the form is submitted. For instance, if your action looks
 * like this: `public function submitAction(ExampleForm $exampleForm) {...}`,
 * then the "name" attribute of this view helper must be "exampleForm".
 *
 * Thanks to the information of the form, the following things are automatically
 * handled in this view helper:
 *
 * - Class
 *   A custom class may be added to the form DOM element. If the TypoScript
 *   configuration "settings.defaultClass" is set for this form, then the given
 *   class will be added to the form element.
 *
 * - JavaScript
 *   A block of JavaScript is built from scratch, which will initialize the
 *   form, add validation rules to the fields, and handle activation of the
 *   fields validation.
 *
 * - Data attributes
 *   To help integrators customize every aspect they need in CSS, every useful
 *   information is put in data attributes in the form DOM element. For example,
 *   you can know in real time if the field "email" is valid if the form has the
 *   attribute "formz-valid-email"
 *
 * - CSS
 *   A block of CSS is built from scratch, which will handle the fields display,
 *   depending on their activation property.
 */
class FormViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\FormViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @var PageRenderer
     */
    protected $pageRenderer;

    /**
     * @var FormObjectFactory
     */
    protected $formObjectFactory;

    /**
     * @var FormService
     */
    protected $formService;

    /**
     * @var string
     */
    protected $formObjectClassName;

    /**
     * @var AssetHandlerFactory
     */
    protected $assetHandlerFactory;

    /**
     * @var TimeTrackerService
     */
    protected $timeTracker;

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();

        /*
         * Important: we need to instantiate the page renderer with this instead
         * of Extbase object manager (or with an inject function).
         *
         * This is due to some TYPO3 low level behaviour which overrides the
         * page renderer singleton instance, whenever a new request is used. The
         * problem is that the instance is not updated on Extbase side.
         *
         * Using Extbase injection can lead to old page renderer instance being
         * used, resulting in a leak of assets inclusion, and maybe more issues.
         */
        $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
    }

    /**
     * @inheritdoc
     */
    public function initializeArguments()
    {
        parent::initializeArguments();

        // The name attribute becomes mandatory.
        $this->overrideArgument('name', 'string', 'Name of the form', true);
        $this->registerArgument('formClassName', 'string', 'Class name of the form.', false);
    }

    /**
     * @return string
     */
    protected function renderViewHelper()
    {
        $this->timeTracker = TimeTrackerService::getAndStart();
        $result = '';

        if (false === ContextService::get()->isTypoScriptIncluded()) {
            if (ExtensionService::get()->isInDebugMode()) {
                $result = ContextService::get()->translate('form.typoscript_not_included.error_message');
            }
        } else {
            $formObject = $this->formObjectFactory->getInstanceFromClassName($this->getFormObjectClassName(), $this->getFormObjectName());

            $this->formService->setFormObject($formObject);
            $formzValidationResult = $formObject->getConfigurationValidationResult();

            if ($formzValidationResult->hasErrors()) {
                // If the form configuration is not valid, we display the errors list.
                $result = $this->getErrorText($formzValidationResult);
            } else {
                // Everything is ok, we render the form.
                $result = $this->renderForm(func_get_args());
            }

            unset($formzValidationResult);
        }

        $this->timeTracker->logTime('final');
        $result = $this->timeTracker->getHTMLCommentLogs() . LF . $result;
        unset($this->timeTracker);

        $this->formService->resetState();

        return $result;
    }
    /**
     * Will render the whole form and return the HTML result.
     *
     * @param array $arguments
     * @return string
     */
    final protected function renderForm(array $arguments)
    {
        $this->timeTracker->logTime('post-config');

        $this->assetHandlerFactory = AssetHandlerFactory::get($this->formService->getFormObject(), $this->controllerContext);

        $this->setObjectAndRequestResult()
            ->applyBehavioursOnSubmittedForm()
            ->addDefaultClass()
            ->handleDataAttributes();

        $assetHandlerConnectorManager = AssetHandlerConnectorManager::get($this->pageRenderer, $this->assetHandlerFactory);
        $assetHandlerConnectorManager->includeDefaultAssets();
        $assetHandlerConnectorManager->getJavaScriptAssetHandlerConnector()
            ->generateAndIncludeFormzConfigurationJavaScript()
            ->generateAndIncludeJavaScript()
            ->generateAndIncludeInlineJavaScript()
            ->includeJavaScriptValidationAndConditionFiles();
        $assetHandlerConnectorManager->getCssAssetHandlerConnector()->includeGeneratedCss();

        $this->timeTracker->logTime('pre-render');

        // Renders the whole Fluid template.
        $result = call_user_func_array([get_parent_class(), 'render'], $arguments);

        $assetHandlerConnectorManager->getJavaScriptAssetHandlerConnector()->includeLanguageJavaScriptFiles();

        return $result;
    }

    /**
     * This function will inject in the variable container the instance of form
     * and its submission result. There are only two ways to be sure the values
     * injected are correct: when the form has actually been submitted by the
     * user, or when the view helper argument `object` is filled.
     *
     * @return $this
     */
    protected function setObjectAndRequestResult()
    {
        $this->formService->activateFormContext();

        $originalRequest = $this->controllerContext
            ->getRequest()
            ->getOriginalRequest();

        if (null !== $originalRequest
            && $originalRequest->hasArgument($this->getFormObjectName())
        ) {
            /** @var array $formInstance */
            $formInstance = $originalRequest->getArgument($this->getFormObjectName());

            $this->formService->setFormInstance($formInstance);
            $this->formService->setFormResult($this->formService->getFormObject()->getLastValidationResult());
            $this->formService->markFormAsSubmitted();
        } elseif (null !== $this->arguments['object']) {
            $formInstance = $this->arguments['object'];

            /*
             * @todo: pas forcément un DefaultFormValidator: comment je gère ça?
             * + ça prend quand même un peu de temps cette manière. Peut-on faire autrement ?
             */
            /** @var DefaultFormValidator $formValidator */
            $formValidator = Core::instantiate(
                DefaultFormValidator::class,
                ['name' => $this->getFormObjectName()]
            );
            $formRequestResult = $formValidator->validate($formInstance);

            $this->formService->setFormInstance($formInstance);
            $this->formService->setFormResult($formRequestResult);
        }

        return $this;
    }

    /**
     * Will loop on the submitted form fields and apply behaviours if their
     * configuration contains.
     *
     * @return $this
     */
    protected function applyBehavioursOnSubmittedForm()
    {
        $originalRequest = $this->controllerContext
            ->getRequest()
            ->getOriginalRequest();

        if ($this->formService->formWasSubmitted()) {
            /** @var BehavioursManager $behavioursManager */
            $behavioursManager = GeneralUtility::makeInstance(BehavioursManager::class);

            $formProperties = $behavioursManager->applyBehaviourOnPropertiesArray(
                $this->formService->getFormInstance(),
                $this->formService->getFormObject()->getConfiguration()
            );

            $originalRequest->setArgument($this->getFormObjectName(), $formProperties);
        }

        return $this;
    }

    /**
     * Will add a default class to the form element.
     *
     * To customize the class, take a look at `settings.defaultClass` in the
     * form TypoScript configuration.
     *
     * @return $this
     */
    protected function addDefaultClass()
    {
        $formDefaultClass = $this->formService
            ->getFormObject()
            ->getConfiguration()
            ->getSettings()
            ->getDefaultClass();

        $class = $this->tag->getAttribute('class');

        if (false === empty($formDefaultClass)) {
            $class = ((!empty($class)) ? $class . ' ' : '') . $formDefaultClass;
        }

        $this->tag->addAttribute('class', $class);

        return $this;
    }

    /**
     * Adds custom data attributes to the form element, based on the
     * submitted form values and results.
     *
     * @return $this
     */
    protected function handleDataAttributes()
    {
        $object = $this->formService->getFormInstance();
        $formResult = $this->formService->getFormResult();

        /** @var DataAttributesAssetHandler $dataAttributesAssetHandler */
        $dataAttributesAssetHandler =  $this->assetHandlerFactory->getAssetHandler(DataAttributesAssetHandler::class);

        $dataAttributes = [];
        if ($object) {
            $dataAttributes += $dataAttributesAssetHandler->getFieldsValuesDataAttributes($object, $formResult);
        }

        if ($formResult) {
            $dataAttributes += $dataAttributesAssetHandler->getFieldsValidDataAttributes($formResult);

            if (true === $this->formService->formWasSubmitted()) {
                $dataAttributes += ['formz-submission-done' => '1'];
                $dataAttributes += $dataAttributesAssetHandler->getFieldsMessagesDataAttributes($formResult);
            }
        }

        foreach ($dataAttributes as $attributeName => $attributeValue) {
            $this->tag->addAttribute($attributeName, $attributeValue);
        }

        return $this;
    }

    /**
     * Will return an error text from a Fluid view.
     *
     * @param Result $result
     * @return string
     */
    protected function getErrorText(Result $result)
    {
        /** @var $view \TYPO3\CMS\Fluid\View\StandaloneView */
        $view = $this->objectManager->get(StandaloneView::class);
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName('EXT:' . ExtensionService::get()->getExtensionKey() . '/Resources/Private/Templates/Error/ConfigurationErrorBlock.html'));
        $layoutRootPath = StringService::get()->getExtensionRelativePath('Resources/Private/Layouts');
        $view->setLayoutRootPaths([$layoutRootPath]);
        $view->assign('result', $result);

        $templatePath = GeneralUtility::getFileAbsFileName('EXT:' . ExtensionService::get()->getExtensionKey() . '/Resources/Public/StyleSheets/Form.ErrorBlock.css');
        $this->pageRenderer->addCssFile(StringService::get()->getResourceRelativePath($templatePath));

        return $view->render();
    }

    /**
     * Returns the class name of the form object: it is fetched from the action
     * of the controller which will be called when submitting this form. It
     * means two things:
     * - The action must have a parameter which has the exact same name as the
     *   form.
     * - The parameter must indicate its type.
     *
     * @return null|string
     * @throws \Exception
     */
    protected function getFormObjectClassName()
    {
        if (null === $this->formObjectClassName) {
            $request = $this->controllerContext->getRequest();
            $controllerObjectName = $request->getControllerObjectName();
            $actionName = ($this->arguments['action']) ?: $request->getControllerActionName();
            $actionName = $actionName . 'Action';

            if ($this->hasArgument('formClassName')) {
                $formClassName = $this->arguments['formClassName'];
            } else {
                /** @var ReflectionService $reflectionService */
                $reflectionService = $this->objectManager->get(ReflectionService::class);
                $methodParameters = $reflectionService->getMethodParameters($controllerObjectName, $actionName);

                if (false === isset($methodParameters[$this->getFormObjectName()])) {
                    throw new \Exception(
                        'The method "' . $controllerObjectName . '::' . $actionName . '()" must have a parameter "$' . $this->getFormObjectName() . '". Note that you can also change the parameter "name" of the form view helper.',
                        1457441846
                    );
                }

                $formClassName = $methodParameters[$this->getFormObjectName()]['type'];
            }

            if (false === class_exists($formClassName)) {
                throw new \Exception(
                    'Invalid value for the form class name (current value: "' . $formClassName . '"). You need to either fill the parameter "formClassName" in the view helper, or specify the type of the parameter "$' . $this->getFormObjectName() . '" for the method "' . $controllerObjectName . '::' . $actionName . '()".',
                    1457442014
                );
            }

            if (false === in_array(FormInterface::class, class_implements($formClassName))) {
                throw new \Exception(
                    'Invalid value for the form class name (current value: "' . $formClassName . '"); it must be an instance of "' . FormInterface::class . '".',
                    1457442462
                );
            }

            $this->formObjectClassName = $formClassName;
        }

        return $this->formObjectClassName;
    }

    /**
     * @param PageRenderer $pageRenderer
     */
    public function injectPageRenderer(PageRenderer $pageRenderer)
    {
        $this->pageRenderer = $pageRenderer;
    }

    /**
     * @param FormObjectFactory $formObjectFactory
     */
    public function injectFormObjectFactory(FormObjectFactory $formObjectFactory)
    {
        $this->formObjectFactory = $formObjectFactory;
    }

    /**
     * @param FormService $service
     */
    public function injectFormService(FormService $service)
    {
        $this->formService = $service;
    }
}
