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

namespace Romm\Formz\AssetHandler\Connector;

use Romm\Formz\AssetHandler\AbstractAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FieldsActivationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FieldsValidationActivationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FieldsValidationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FormInitializationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FormRequestDataJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FormzConfigurationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FormzLocalizationJavaScriptAssetHandler;
use Romm\Formz\Condition\Processor\ConditionProcessorFactory;
use Romm\Formz\Core\Core;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

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
     * @param AssetHandlerConnectorManager $assetHandlerConnectorManager
     */
    public function __construct(AssetHandlerConnectorManager $assetHandlerConnectorManager)
    {
        $this->assetHandlerConnectorManager = $assetHandlerConnectorManager;
    }

    /**
     * Will include all default JavaScript files declared in the property
     * `$javaScriptFiles` of this class, as well as the main Formz
     * configuration.
     *
     * @return $this
     */
    public function includeDefaultJavaScriptFiles()
    {
        if (Core::get()->isInDebugMode()) {
            $this->javaScriptFiles[] = 'Formz.Debug.js';
        }

        foreach ($this->javaScriptFiles as $file) {
            $filePath = Core::get()->getExtensionRelativePath('Resources/Public/JavaScript/' . $file);

            $this->assetHandlerConnectorManager
                ->getPageRenderer()
                ->addJsFile($filePath);
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
        $filePath = $this->assetHandlerConnectorManager->getFormzGeneratedFilePath('local-' . Core::get()->getLanguageKey()) . '.js';

        $this->assetHandlerConnectorManager->createFileInTemporaryDirectory(
            $filePath,
            function() {
                /** @var FormzLocalizationJavaScriptAssetHandler $formzLocalizationJavaScriptAssetHandler */
                $formzLocalizationJavaScriptAssetHandler = $this->assetHandlerConnectorManager
                    ->getAssetHandlerFactory()
                    ->getAssetHandler(FormzLocalizationJavaScriptAssetHandler::class);

                return $formzLocalizationJavaScriptAssetHandler
                    ->injectTranslationsForFormFieldsValidation()
                    ->getJavaScriptCode();
            }
        );

        $this->assetHandlerConnectorManager
            ->getPageRenderer()
            ->addJsFooterFile($filePath);

        return $this;
    }

    /**
     * Includes Formz configuration JavaScript declaration. If the file exists,
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

        $this->assetHandlerConnectorManager
            ->getPageRenderer()
            ->addJsFooterFile($fileName);

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

        $this->assetHandlerConnectorManager
            ->getPageRenderer()
            ->addJsFooterFile($filePath);

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
        $formClassName = $this->assetHandlerConnectorManager
            ->getAssetHandlerFactory()
            ->getFormObject()
            ->getClassName();

        $javaScriptCode = $this->getFormRequestDataJavaScriptAssetHandler()
            ->getFormRequestDataJavaScriptCode();

        if (Core::get()->isInDebugMode()) {
            $javaScriptCode .= LF;
            $javaScriptCode .= 'Formz.Debug.activate();';
        }

        /** @var UriBuilder $uriBuilder */
        $uriBuilder = Core::get()->getObjectManager()->get(UriBuilder::class);
        $uri = $uriBuilder->reset()
            ->setTargetPageType(1473682545)
            ->setNoCache(true)
            ->setUseCacheHash(false)
            ->setCreateAbsoluteUri(true)
            ->build();

        $javaScriptCode .= LF;
        $javaScriptCode .= "Formz.setAjaxUrl('$uri');";

        $this->assetHandlerConnectorManager
            ->getPageRenderer()
            ->addJsFooterInlineCode('Formz - Initialization ' . $formClassName, $javaScriptCode);

        return $this;
    }

    /**
     * Will include all new JavaScript files given, by checking that every given
     * file was not already included.
     *
     * @return $this
     */
    public function includeJavaScriptValidationFiles()
    {
        $javaScriptValidationFiles = $this->getJavaScriptFiles();
        $assetHandlerConnectorStates = $this->assetHandlerConnectorManager->getAssetHandlerConnectorStates();

        foreach ($javaScriptValidationFiles as $file) {
            if (false === in_array($file, $assetHandlerConnectorStates->getAlreadyIncludedValidationJavaScriptFiles())) {
                $path = Core::get()->getResourceRelativePath($file);
                $this->assetHandlerConnectorManager->getPageRenderer()->addJsFooterFile($path);
                $assetHandlerConnectorStates->registerIncludedValidationJavaScriptFiles($file);
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
        $formObject = $this->assetHandlerConnectorManager
            ->getAssetHandlerFactory()
            ->getFormObject();

        $javaScriptFiles = $this->getFieldsValidationJavaScriptAssetHandler()
            ->getJavaScriptValidationFiles();

        $conditionProcessor = ConditionProcessorFactory::getInstance()
            ->get($formObject);

        $javaScriptFiles = array_merge($javaScriptFiles, $conditionProcessor->getJavaScriptFiles());

        return $javaScriptFiles;
    }

    /**
     * @return FormzConfigurationJavaScriptAssetHandler|AbstractAssetHandler
     */
    protected function getFormzConfigurationJavaScriptAssetHandler()
    {
        return $this->assetHandlerConnectorManager
            ->getAssetHandlerFactory()
            ->getAssetHandler(FormzConfigurationJavaScriptAssetHandler::class);
    }

    /**
     * @return FormInitializationJavaScriptAssetHandler|AbstractAssetHandler
     */
    protected function getFormInitializationJavaScriptAssetHandler()
    {
        return $this->assetHandlerConnectorManager
            ->getAssetHandlerFactory()
            ->getAssetHandler(FormInitializationJavaScriptAssetHandler::class);
    }

    /**
     * @return FieldsValidationJavaScriptAssetHandler|AbstractAssetHandler
     */
    protected function getFieldsValidationJavaScriptAssetHandler()
    {
        return $this->assetHandlerConnectorManager
            ->getAssetHandlerFactory()
            ->getAssetHandler(FieldsValidationJavaScriptAssetHandler::class);
    }

    /**
     * @return FieldsActivationJavaScriptAssetHandler|AbstractAssetHandler
     */
    protected function getFieldsActivationJavaScriptAssetHandler()
    {
        return $this->assetHandlerConnectorManager
            ->getAssetHandlerFactory()
            ->getAssetHandler(FieldsActivationJavaScriptAssetHandler::class);
    }

    /**
     * @return FieldsValidationActivationJavaScriptAssetHandler|AbstractAssetHandler
     */
    protected function getFieldsValidationActivationJavaScriptAssetHandler()
    {
        return $this->assetHandlerConnectorManager
            ->getAssetHandlerFactory()
            ->getAssetHandler(FieldsValidationActivationJavaScriptAssetHandler::class);
    }

    /**
     * @return FormRequestDataJavaScriptAssetHandler|AbstractAssetHandler
     */
    protected function getFormRequestDataJavaScriptAssetHandler()
    {
        return $this->assetHandlerConnectorManager
            ->getAssetHandlerFactory()
            ->getAssetHandler(FormRequestDataJavaScriptAssetHandler::class);
    }
}
