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

namespace Romm\Formz\AssetHandler\Connector;

use Romm\Formz\AssetHandler\AbstractAssetHandler;
use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\AssetHandler\JavaScript\FieldsActivationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FieldsValidationActivationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FieldsValidationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FormInitializationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FormRequestDataJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FormzConfigurationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FormzLocalizationJavaScriptAssetHandler;
use Romm\Formz\Condition\Processor\ConditionProcessor;
use Romm\Formz\Condition\Processor\ConditionProcessorFactory;
use Romm\Formz\Core\Core;
use Romm\Formz\Form\FormObject;
use Romm\Formz\Service\ContextService;
use Romm\Formz\Service\ExtensionService;
use Romm\Formz\Service\StringService;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Service\EnvironmentService;

class JavaScriptAssetHandlerConnector
{
    /**
     * List of JavaScript files which will be included whenever this view helper
     * is used.
     *
     * @var array
     */
    private $javaScriptFiles = [
        'Formz.Main.js',
        'Formz.Misc.js',
        'Formz.EventsManager.js',
        'Formz.Result.js',
        'Formz.Localization.js',
        'Form/Formz.Form.js',
        'Form/Formz.Form.SubmissionService.js',
        'Field/Formz.Field.js',
        'Field/Formz.Field.DataAttributesService.js',
        'Field/Formz.Field.ValidationService.js',
        'Conditions/Formz.Condition.js',
        'Validators/Formz.Validation.js',
        'Validators/Formz.Validator.Ajax.js'
    ];

    /**
     * @var AssetHandlerConnectorManager
     */
    private $assetHandlerConnectorManager;

    /**
     * @var AssetHandlerFactory
     */
    private $assetHandlerFactory;

    /**
     * @var EnvironmentService
     */
    protected $environmentService;
    /**
     * @param AssetHandlerConnectorManager $assetHandlerConnectorManager
     */
    public function __construct(AssetHandlerConnectorManager $assetHandlerConnectorManager)
    {
        $this->assetHandlerConnectorManager = $assetHandlerConnectorManager;
        $this->assetHandlerFactory = $assetHandlerConnectorManager->getAssetHandlerFactory();
    }

    /**
     * Will include all default JavaScript files declared in the property
     * `$javaScriptFiles` of this class, as well as the main FormZ
     * configuration.
     *
     * @return $this
     */
    public function includeDefaultJavaScriptFiles()
    {
        if (ExtensionService::get()->isInDebugMode()) {
            $this->javaScriptFiles[] = 'Formz.Debug.js';
        }

        foreach ($this->javaScriptFiles as $file) {
            $filePath = StringService::get()->getExtensionRelativePath('Resources/Public/JavaScript/' . $file);

            $this->includeJsFile($filePath);
        }

        return $this;
    }

    /**
     * This function will handle the JavaScript language files.
     *
     * A file will be created for the current language (there can be as many
     * files as languages), containing the translations handling for JavaScript.
     * If the file already exists, it is directly included.
     *
     * @return $this
     */
    public function includeLanguageJavaScriptFiles()
    {
        $filePath = $this->assetHandlerConnectorManager->getFormzGeneratedFilePath('locale-' . ContextService::get()->getLanguageKey()) . '.js';

        $this->assetHandlerConnectorManager->createFileInTemporaryDirectory(
            $filePath,
            function () {
                return $this->getFormzLocalizationJavaScriptAssetHandler()
                    ->injectTranslationsForFormFieldsValidation()
                    ->getJavaScriptCode();
            }
        );

        $this->includeJsFile(StringService::get()->getResourceRelativePath($filePath));

        return $this;
    }

    /**
     * Includes FormZ configuration JavaScript declaration. If the file exists,
     * it is directly included, otherwise the JavaScript code is calculated,
     * then put in the cache file.
     *
     * @return $this
     */
    public function generateAndIncludeFormzConfigurationJavaScript()
    {
        $formzConfigurationJavaScriptAssetHandler = $this->getFormzConfigurationJavaScriptAssetHandler();
        $fileName = $formzConfigurationJavaScriptAssetHandler->getJavaScriptFileName();

        $this->assetHandlerConnectorManager->createFileInTemporaryDirectory(
            $fileName,
            function () use ($formzConfigurationJavaScriptAssetHandler) {
                return $formzConfigurationJavaScriptAssetHandler->getJavaScriptCode();
            }
        );

        $this->includeJsFile(StringService::get()->getResourceRelativePath($fileName));

        return $this;
    }

    /**
     * Will include the generated JavaScript, from multiple asset handlers
     * sources.
     *
     * @return $this
     */
    public function generateAndIncludeJavaScript()
    {
        $filePath = $this->assetHandlerConnectorManager->getFormzGeneratedFilePath() . '.js';

        $this->assetHandlerConnectorManager->createFileInTemporaryDirectory(
            $filePath,
            function () {
                return
                    // Form initialization code.
                    $this->getFormInitializationJavaScriptAssetHandler()
                        ->getFormInitializationJavaScriptCode() .
                    LF .
                    // Fields validation code.
                    $this->getFieldsValidationJavaScriptAssetHandler()
                        ->getJavaScriptCode() .
                    LF .
                    // Fields activation conditions code.
                    $this->getFieldsActivationJavaScriptAssetHandler()
                        ->getFieldsActivationJavaScriptCode() .
                    LF .
                    // Fields validation activation conditions code.
                    $this->getFieldsValidationActivationJavaScriptAssetHandler()
                        ->getFieldsValidationActivationJavaScriptCode();
            }
        );

        $this->includeJsFile(StringService::get()->getResourceRelativePath($filePath));

        return $this;
    }

    /**
     * Here we generate the JavaScript code containing the submitted values, and
     * the existing errors, which is dynamically created at each request.
     *
     * The code is then injected as inline code in the DOM.
     *
     * @return $this
     */
    public function generateAndIncludeInlineJavaScript()
    {
        $formName = $this->assetHandlerFactory->getFormObject()->getName();

        $javaScriptCode = $this->getFormRequestDataJavaScriptAssetHandler()
            ->getFormRequestDataJavaScriptCode();

        if (ExtensionService::get()->isInDebugMode()) {
            $javaScriptCode .= LF . $this->getDebugActivationCode();
        }

        $uri = $this->getAjaxUrl();

        $javaScriptCode .= LF;
        $javaScriptCode .= <<<JS
Fz.setAjaxUrl('$uri');
JS;

        $this->addInlineJs('FormZ - Initialization ' . $formName, $javaScriptCode);

        return $this;
    }

    /**
     * Will include all new JavaScript files given, by checking that every given
     * file was not already included.
     *
     * @return $this
     */
    public function includeJavaScriptValidationAndConditionFiles()
    {
        $javaScriptValidationFiles = $this->getJavaScriptFiles();
        $assetHandlerConnectorStates = $this->assetHandlerConnectorManager
            ->getAssetHandlerConnectorStates();

        foreach ($javaScriptValidationFiles as $file) {
            if (false === in_array($file, $assetHandlerConnectorStates->getAlreadyIncludedValidationJavaScriptFiles())) {
                $assetHandlerConnectorStates->registerIncludedValidationJavaScriptFiles($file);
                $this->includeJsFile(StringService::get()->getResourceRelativePath($file));
            }
        }

        return $this;
    }

    /**
     * Returns the list of JavaScript files which are used for the current form
     * object.
     *
     * @return array
     */
    protected function getJavaScriptFiles()
    {
        $formObject = $this->assetHandlerFactory->getFormObject();

        $javaScriptFiles = $this->getFieldsValidationJavaScriptAssetHandler()
            ->getJavaScriptValidationFiles();

        $conditionProcessor = $this->getConditionProcessor($formObject);

        $javaScriptFiles = array_merge($javaScriptFiles, $conditionProcessor->getJavaScriptFiles());

        return $javaScriptFiles;
    }

    /**
     * We need an abstraction function because the footer inclusion for assets
     * does not work in backend. It means we include every JavaScript asset in
     * the header when the request is in a backend context.
     *
     * @see https://forge.typo3.org/issues/60213
     *
     * @param string $path
     */
    protected function includeJsFile($path)
    {
        $pageRenderer = $this->assetHandlerConnectorManager->getPageRenderer();

        if ($this->environmentService->isEnvironmentInFrontendMode()) {
            $pageRenderer->addJsFooterFile($path);
        } else {
            $pageRenderer->addJsFile($path);
        }
    }

    /**
     * @see includeJsFile()
     *
     * @param string $name
     * @param string $javaScriptCode
     */
    protected function addInlineJs($name, $javaScriptCode)
    {
        $pageRenderer = $this->assetHandlerConnectorManager->getPageRenderer();

        if ($this->environmentService->isEnvironmentInFrontendMode()) {
            $pageRenderer->addJsFooterInlineCode($name, $javaScriptCode);
        } else {
            $pageRenderer->addJsInlineCode($name, $javaScriptCode);
        }
    }

    /**
     * @return string
     */
    protected function getAjaxUrl()
    {
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = Core::instantiate(UriBuilder::class);

        return $uriBuilder->reset()
            ->setTargetPageType(1473682545)
            ->setNoCache(true)
            ->setUseCacheHash(false)
            ->setCreateAbsoluteUri(true)
            ->build();
    }

    /**
     * @return string
     */
    protected function getDebugActivationCode()
    {
        return 'Fz.Debug.activate();';
    }

    /**
     * @param FormObject $formObject
     * @return ConditionProcessor
     */
    protected function getConditionProcessor(FormObject $formObject)
    {
        return ConditionProcessorFactory::getInstance()
            ->get($formObject);
    }

    /**
     * @return FormzConfigurationJavaScriptAssetHandler|AbstractAssetHandler
     */
    protected function getFormzConfigurationJavaScriptAssetHandler()
    {
        return $this->assetHandlerFactory->getAssetHandler(FormzConfigurationJavaScriptAssetHandler::class);
    }

    /**
     * @return FormInitializationJavaScriptAssetHandler|AbstractAssetHandler
     */
    protected function getFormInitializationJavaScriptAssetHandler()
    {
        return $this->assetHandlerFactory->getAssetHandler(FormInitializationJavaScriptAssetHandler::class);
    }

    /**
     * @return FieldsValidationJavaScriptAssetHandler|AbstractAssetHandler
     */
    protected function getFieldsValidationJavaScriptAssetHandler()
    {
        return $this->assetHandlerFactory->getAssetHandler(FieldsValidationJavaScriptAssetHandler::class);
    }

    /**
     * @return FieldsActivationJavaScriptAssetHandler|AbstractAssetHandler
     */
    protected function getFieldsActivationJavaScriptAssetHandler()
    {
        return $this->assetHandlerFactory->getAssetHandler(FieldsActivationJavaScriptAssetHandler::class);
    }

    /**
     * @return FieldsValidationActivationJavaScriptAssetHandler|AbstractAssetHandler
     */
    protected function getFieldsValidationActivationJavaScriptAssetHandler()
    {
        return $this->assetHandlerFactory->getAssetHandler(FieldsValidationActivationJavaScriptAssetHandler::class);
    }

    /**
     * @return FormRequestDataJavaScriptAssetHandler|AbstractAssetHandler
     */
    protected function getFormRequestDataJavaScriptAssetHandler()
    {
        return $this->assetHandlerFactory->getAssetHandler(FormRequestDataJavaScriptAssetHandler::class);
    }

    /**
     * @return FormzLocalizationJavaScriptAssetHandler|AbstractAssetHandler
     */
    protected function getFormzLocalizationJavaScriptAssetHandler()
    {
        return $this->assetHandlerFactory->getAssetHandler(FormzLocalizationJavaScriptAssetHandler::class);
    }

    /**
     * @param EnvironmentService $environmentService
     */
    public function injectEnvironmentService(EnvironmentService $environmentService)
    {
        $this->environmentService = $environmentService;
    }
}
