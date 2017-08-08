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

use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\AssetHandler\Connector\AssetHandlerConnectorManager;
use Romm\Formz\AssetHandler\Html\DataAttributesAssetHandler;
use Romm\Formz\Core\Core;
use Romm\Formz\Exceptions\ClassNotFoundException;
use Romm\Formz\Exceptions\InvalidOptionValueException;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Form\FormObject\FormObjectFactory;
use Romm\Formz\Service\ContextService;
use Romm\Formz\Service\ControllerService;
use Romm\Formz\Service\ExtensionService;
use Romm\Formz\Service\FormService;
use Romm\Formz\Service\StringService;
use Romm\Formz\Service\TimeTrackerService;
use Romm\Formz\Service\ViewHelper\Form\FormViewHelperService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
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
 *   attribute "fz-valid-email"
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
     * @var FormObject
     */
    protected $formObject;

    /**
     * @var FormViewHelperService
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
     * @var bool
     */
    protected $typoScriptIncluded = false;

    /**
     * @var ControllerService
     */
    protected $controllerService;

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();

        $this->typoScriptIncluded = ContextService::get()->isTypoScriptIncluded();

        if (true === $this->typoScriptIncluded) {
            $this->timeTracker = TimeTrackerService::getAndStart();

            $this->formObjectClassName = $this->getFormClassName();
            $this->formObject = $this->getFormObject($this->getFormInstance());
            $this->timeTracker->logTime('post-config');

            $this->assetHandlerFactory = AssetHandlerFactory::get($this->formObject, $this->controllerContext);

            /** @var Request $request */
            $request = $this->controllerContext->getRequest();

            $this->formService->setFormObject($this->formObject);
            $this->formService->setRequest($request);
            $this->formService->injectFormRequestData();
        }

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
        $this->overrideArgument('name', 'string', 'Name of the form.', true);
        $this->registerArgument('formClassName', 'string', 'Class name of the form.', false);
    }

    /**
     * @return string
     */
    protected function renderViewHelper()
    {
        if (false === $this->typoScriptIncluded) {
            return (ExtensionService::get()->isInDebugMode())
                ? ContextService::get()->translate('form.typoscript_not_included.error_message')
                : '';
        }

        $result = ($this->formObject->getDefinitionValidationResult()->hasErrors())
            // If the form configuration is not valid, we display the errors list.
            ? $this->getErrorText()
            // Everything is ok, we render the form.
            : $this->renderForm(func_get_args());

        $this->timeTracker->logTime('final');

        if (ExtensionService::get()->isInDebugMode()) {
            $result = $this->timeTracker->getHTMLCommentLogs() . LF . $result;
        }

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
        /*
         * We begin by setting up the form service: request results and form
         * instance are inserted in the service, and are used afterwards.
         *
         * There are only two ways to be sure the values injected are correct:
         * when the form was actually submitted by the user, or when the
         * argument `object` of the view helper is filled with a form instance.
         */
        $this->formService->activateFormContext();

        /*
         * If the form was submitted, applying custom behaviours on its fields.
         */
        $this->formService->applyBehavioursOnSubmittedForm();

        /*
         * Adding the default class configured in TypoScript configuration to
         * the form HTML tag.
         */
        $this->addDefaultClass();

        /*
         * Handling data attributes that are added to the form HTML tag,
         * depending on several parameters.
         */
        $this->handleDataAttributes();

        /*
         * Including JavaScript and CSS assets in the page renderer.
         */
        $this->handleAssets();

        $this->timeTracker->logTime('pre-render');

        /*
         * Getting the result of the original Fluid `FormViewHelper` rendering.
         */
        $result = $this->getParentRenderResult($arguments);
        $renderingResult = $this->formService->getResult();

        if ($renderingResult->hasErrors()) {
            $result = $this->getErrorText($renderingResult);
        }

        /*
         * Language files need to be included at the end, because they depend on
         * what was used by previous assets.
         */
        $this->getAssetHandlerConnectorManager()
            ->getJavaScriptAssetHandlerConnector()
            ->includeLanguageJavaScriptFiles();

        return $result;
    }

    /**
     * Adds a hidden field to the form rendering, containing the form request
     * data as a hashed string (which can be retrieved and used later).
     *
     * @return string
     */
    protected function renderHiddenReferrerFields()
    {
        $result = parent::renderHiddenReferrerFields();

        $requestData = $this->formObject->getRequestData();
        $requestData->setFormHash($this->formObject->getFormHash());
        $value = htmlspecialchars($this->hashService->appendHmac(base64_encode(serialize($requestData->toArray()))));

        $result .= '<input type="hidden" name="' . $this->prefixFieldName('formData') . '" value="' . $value . '" />' . LF;

        return $result;
    }

    /**
     * Will add a default class to the form element.
     *
     * To customize the class, take a look at `settings.defaultClass` in the
     * form TypoScript configuration.
     */
    protected function addDefaultClass()
    {
        $formDefaultClass = $this->formObject
            ->getDefinition()
            ->getSettings()
            ->getDefaultClass();

        $class = $this->tag->getAttribute('class');

        if (false === empty($formDefaultClass)) {
            $class = (!empty($class) ? $class . ' ' : '') . $formDefaultClass;
            $this->tag->addAttribute('class', $class);
        }
    }

    /**
     * Adds data attributes to the form element, based on several statements,
     * like the submitted form values, the validation result and others.
     */
    protected function handleDataAttributes()
    {
        $dataAttributesAssetHandler = $this->getDataAttributesAssetHandler();
        $dataAttributes = $this->formService->getDataAttributes($dataAttributesAssetHandler);

        $this->tag->addAttributes($dataAttributes);
    }

    /**
     * Will include all JavaScript and CSS assets needed for this form.
     */
    protected function handleAssets()
    {
        $assetHandlerConnectorManager = $this->getAssetHandlerConnectorManager();

        // Default FormZ assets.
        $assetHandlerConnectorManager->includeDefaultAssets();

        // JavaScript assets.
        $assetHandlerConnectorManager->getJavaScriptAssetHandlerConnector()
            ->generateAndIncludeFormzConfigurationJavaScript()
            ->generateAndIncludeJavaScript()
            ->generateAndIncludeInlineJavaScript()
            ->includeJavaScriptValidationAndConditionFiles();

        // CSS assets.
        $assetHandlerConnectorManager->getCssAssetHandlerConnector()
            ->includeGeneratedCss();
    }

    /**
     * Will return an error text from a Fluid view.
     *
     * @param Result $renderingResult
     * @return string
     */
    protected function getErrorText(Result $renderingResult = null)
    {
        /** @var $view StandaloneView */
        $view = Core::instantiate(StandaloneView::class);
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName('EXT:' . ExtensionService::get()->getExtensionKey() . '/Resources/Private/Templates/Error/ConfigurationErrorBlock.html'));
        $layoutRootPath = StringService::get()->getExtensionRelativePath('Resources/Private/Layouts');
        $partialRootPath = StringService::get()->getExtensionRelativePath('Resources/Private/Partials');
        $view->setLayoutRootPaths([$layoutRootPath]);
        $view->setPartialRootPaths([$partialRootPath]);
        $view->assign('formObject', $this->formObject);
        $view->assign('renderingResult', $renderingResult);

        return $view->render();
    }

    /**
     * Checks the type of the argument `object`, and returns it if everything is
     * ok.
     *
     * @return FormInterface|null
     * @throws InvalidOptionValueException
     */
    protected function getFormObjectArgument()
    {
        $objectArgument = $this->arguments['object'];

        if (null === $objectArgument) {
            return null;
        }

        if (false === is_object($objectArgument)) {
            throw InvalidOptionValueException::formViewHelperWrongFormValueType($objectArgument);
        }

        if (false === $objectArgument instanceof FormInterface) {
            throw InvalidOptionValueException::formViewHelperWrongFormValueObjectType($objectArgument);
        }

        $formClassName = $this->getFormClassName();

        if (false === $objectArgument instanceof $formClassName) {
            throw InvalidOptionValueException::formViewHelperWrongFormValueClassName($formClassName, $objectArgument);
        }

        return $objectArgument;
    }

    /**
     * Returns the class name of the form object: it is fetched from the action
     * of the controller which will be called when submitting this form. It
     * means two things:
     * - The action must have a parameter which has the exact same name as the
     *   form;
     * - The parameter must indicate its type.
     *
     * @return string
     * @throws ClassNotFoundException
     * @throws InvalidOptionValueException
     */
    protected function getFormClassName()
    {
        $formClassName = ($this->hasArgument('formClassName'))
            ? $this->arguments['formClassName']
            : $this->getFormClassNameFromControllerAction();

        if (false === class_exists($formClassName)) {
            throw ClassNotFoundException::formViewHelperClassNotFound($formClassName, $this->getFormObjectName(), $this->getControllerName(), $this->getControllerActionName());
        }

        if (false === in_array(FormInterface::class, class_implements($formClassName))) {
            throw InvalidOptionValueException::formViewHelperWrongFormType($formClassName);
        }

        return $formClassName;
    }

    /**
     * Will fetch the name of the controller action argument bound to this
     * request.
     *
     * @return string
     */
    protected function getFormClassNameFromControllerAction()
    {
        return $this->controllerService->getFormClassNameFromControllerAction(
            $this->getControllerName(),
            $this->getControllerActionName(),
            $this->getFormObjectName()
        );
    }

    /**
     * Renders the whole Fluid template.
     *
     * @param array $arguments
     * @return string
     */
    protected function getParentRenderResult(array $arguments)
    {
        return call_user_func_array([get_parent_class(), 'render'], $arguments);
    }

    /**
     * @return string
     */
    protected function getControllerName()
    {
        return ($this->arguments['controller'])
            ?: $this->controllerContext
                ->getRequest()
                ->getControllerObjectName();
    }

    /**
     * @return string
     */
    protected function getControllerActionName()
    {
        return ($this->arguments['action'])
            ?: $this->controllerContext
                ->getRequest()
                ->getControllerActionName();
    }

    /**
     * @return AssetHandlerConnectorManager
     */
    protected function getAssetHandlerConnectorManager()
    {
        return AssetHandlerConnectorManager::get($this->pageRenderer, $this->assetHandlerFactory);
    }

    /**
     * @return DataAttributesAssetHandler
     */
    protected function getDataAttributesAssetHandler()
    {
        /** @var DataAttributesAssetHandler $assetHandler */
        $assetHandler = $this->assetHandlerFactory->getAssetHandler(DataAttributesAssetHandler::class);

        return $assetHandler;
    }

    /**
     * @return FormInterface
     */
    protected function getFormInstance()
    {
        /*
         * If the argument `object` was filled with an instance of Form, it
         * becomes the form instance. Otherwise we try to fetch an instance from
         * the form with errors list. If there is still no form, we create an
         * empty instance.
         */
        $objectArgument = $this->getFormObjectArgument();

        if ($objectArgument) {
            $form = $objectArgument;
        } else {
            $submittedForm = FormService::getFormWithErrors($this->getFormClassName());

            $form = $submittedForm ?: Core::get()->getObjectManager()->getEmptyObject($this->getFormClassName());
        }

        return $form;
    }

    /**
     * @param FormInterface $form
     * @return FormObject
     */
    protected function getFormObject(FormInterface $form)
    {
        return FormObjectFactory::get()->registerAndGetFormInstance($form, $this->getFormObjectName());
    }

    /**
     * @param FormViewHelperService $service
     */
    public function injectFormService(FormViewHelperService $service)
    {
        $this->formService = $service;
    }

    /**
     * @param ControllerService $controllerService
     */
    public function injectControllerService(ControllerService $controllerService)
    {
        $this->controllerService = $controllerService;
    }
}
