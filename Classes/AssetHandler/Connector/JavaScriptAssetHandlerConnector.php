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

use Romm\Formz\AssetHandler\JavaScript\FieldsActivationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FieldsValidationActivationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FieldsValidationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FormInitializationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FormRequestDataJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FormzConfigurationJavaScriptAssetHandler;
use Romm\Formz\AssetHandler\JavaScript\FormzLocalizationJavaScriptAssetHandler;
use Romm\Formz\Condition\Items\AbstractConditionItem;
use Romm\Formz\Condition\Node\ConditionNode;
use Romm\Formz\Core\Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
            $this->assetHandlerConnectorManager->getPageRenderer()->addJsFile($filePath);
        }

        /** @var FormzConfigurationJavaScriptAssetHandler $formzConfigurationJavaScriptAssetHandler */
        $formzConfigurationJavaScriptAssetHandler = $this->assetHandlerConnectorManager
            ->getAssetHandlerFactory()
            ->getAssetHandler(FormzConfigurationJavaScriptAssetHandler::class);

        $formzConfigurationJavaScriptFileName = $formzConfigurationJavaScriptAssetHandler->getJavaScriptFileName();
        $filePath = GeneralUtility::getFileAbsFileName($formzConfigurationJavaScriptFileName);

        if (false === file_exists($filePath)) {
            GeneralUtility::writeFileToTypo3tempDir(
                $filePath,
                $formzConfigurationJavaScriptAssetHandler->getJavaScriptCode()
            );
        }

        $this->assetHandlerConnectorManager->getPageRenderer()->addJsFooterFile($formzConfigurationJavaScriptFileName);

        return $this;
    }

    /**
     * Will take care of generating the JavaScript with the
     * `AssetHandlerFactory`. The code will be put in a `.js` file in the
     * `typo3temp` directory.
     *
     * If the file already exists, it is included directly before the code
     * generation.
     *
     * @return $this
     */
    public function includeGeneratedJavaScript()
    {
        $filePath = $this->assetHandlerConnectorManager->getFormzGeneratedFilePath() . '.js';
        $cacheInstance = Core::get()->getCacheInstance();
        $formClassName = $this->assetHandlerConnectorManager->getAssetHandlerFactory()->getFormObject()->getClassName();
        $javaScriptValidationFilesCacheIdentifier = Core::get()->getCacheIdentifier('js-files-', $formClassName);

        /** @var FieldsValidationJavaScriptAssetHandler $fieldValidationConfigurationAssetHandler */
        $fieldValidationConfigurationAssetHandler = $this->assetHandlerConnectorManager
            ->getAssetHandlerFactory()
            ->getAssetHandler(FieldsValidationJavaScriptAssetHandler::class);

        /** @var FieldsActivationJavaScriptAssetHandler $fieldsActivationJavaScriptAssetHandler */
        $fieldsActivationJavaScriptAssetHandler = $this->assetHandlerConnectorManager
            ->getAssetHandlerFactory()
            ->getAssetHandler(FieldsActivationJavaScriptAssetHandler::class);

        /** @var FieldsValidationActivationJavaScriptAssetHandler $fieldsValidationActivationJavaScriptAssetHandler */
        $fieldsValidationActivationJavaScriptAssetHandler = $this->assetHandlerConnectorManager
            ->getAssetHandlerFactory()
            ->getAssetHandler(FieldsValidationActivationJavaScriptAssetHandler::class);

        if (false === file_exists(GeneralUtility::getFileAbsFileName($filePath))) {
            ConditionNode::distinctUsedConditions();

            /** @var FormInitializationJavaScriptAssetHandler $formInitializationJavaScriptAssetHandler */
            $formInitializationJavaScriptAssetHandler = $this->assetHandlerConnectorManager
                ->getAssetHandlerFactory()
                ->getAssetHandler(FormInitializationJavaScriptAssetHandler::class);

            $javaScriptCode = $formInitializationJavaScriptAssetHandler->getFormInitializationJavaScriptCode() . LF;
            $javaScriptCode .= $fieldValidationConfigurationAssetHandler->process()->getJavaScriptCode() . LF;
            $javaScriptCode .= $fieldsActivationJavaScriptAssetHandler->getFieldsActivationJavaScriptCode() . LF;
            $javaScriptCode .= $fieldsValidationActivationJavaScriptAssetHandler->getFieldsValidationActivationJavaScriptCode();

            GeneralUtility::writeFileToTypo3tempDir(GeneralUtility::getFileAbsFileName($filePath), $javaScriptCode);

            $javaScriptFiles = $this->saveAndGetJavaScriptFiles(
                $javaScriptValidationFilesCacheIdentifier,
                $fieldValidationConfigurationAssetHandler->getJavaScriptValidationFiles()
            );
        } else {
            // Including all JavaScript files required by used validation rules and conditions.
            if ($cacheInstance->has($javaScriptValidationFilesCacheIdentifier)) {
                $javaScriptFiles = $cacheInstance->get($javaScriptValidationFilesCacheIdentifier);
            } else {
                $fieldValidationConfigurationAssetHandler = $fieldValidationConfigurationAssetHandler->process();
                ConditionNode::distinctUsedConditions();
                $fieldsActivationJavaScriptAssetHandler->getFieldsActivationJavaScriptCode();
                $fieldsValidationActivationJavaScriptAssetHandler->getFieldsValidationActivationJavaScriptCode();

                $javaScriptFiles = $this->saveAndGetJavaScriptFiles(
                    $javaScriptValidationFilesCacheIdentifier,
                    $fieldValidationConfigurationAssetHandler->getJavaScriptValidationFiles()
                );
            }
        }

        $this->assetHandlerConnectorManager->getPageRenderer()->addJsFooterFile($filePath);

        $this->includeJavaScriptValidationFiles($javaScriptFiles);

        // Here we generate the JavaScript code containing the submitted values, and the existing errors.
        /** @var FormRequestDataJavaScriptAssetHandler $formRequestDataJavaScriptAssetHandler */
        $formRequestDataJavaScriptAssetHandler = $this->assetHandlerConnectorManager
            ->getAssetHandlerFactory()
            ->getAssetHandler(FormRequestDataJavaScriptAssetHandler::class);

        $javaScriptCode = $formRequestDataJavaScriptAssetHandler->getFormRequestDataJavaScriptCode();

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
     * Will save in cache and return the list of files which must be included in
     * order to make validation rules and conditions work properly.
     *
     * @param string $cacheIdentifier
     * @param array  $javaScriptFiles
     * @return array
     */
    protected function saveAndGetJavaScriptFiles($cacheIdentifier, array $javaScriptFiles)
    {
        /** @var AbstractConditionItem[] $conditions */
        $conditions = ConditionNode::getDistinctUsedConditions();

        foreach ($conditions as $condition) {
            $javaScriptFiles = array_merge($javaScriptFiles, $condition::getJavaScriptFiles());
        }

        Core::get()->getCacheInstance()->set($cacheIdentifier, $javaScriptFiles);

        return $javaScriptFiles;
    }

    /**
     * This function will handle the JavaScript localization files.
     *
     * A file will be created for the current language (there can be as many
     * files as languages), containing the translations handling for JavaScript.
     * If the file already exists, it is directly included.
     *
     * @return $this
     */
    public function handleJavaScriptLocalization()
    {
        $filePath = $this->assetHandlerConnectorManager->getFormzGeneratedFilePath('local-' . Core::get()->getLanguageKey()) . '.js';

        if (false === file_exists(GeneralUtility::getFileAbsFileName($filePath))) {
            /** @var FormzLocalizationJavaScriptAssetHandler $formzLocalizationJavaScriptAssetHandler */
            $formzLocalizationJavaScriptAssetHandler = $this->assetHandlerConnectorManager->getAssetHandlerFactory()->getAssetHandler(FormzLocalizationJavaScriptAssetHandler::class);

            $javaScriptCode = $formzLocalizationJavaScriptAssetHandler
                ->injectTranslationsForFormFieldsValidation()
                ->getJavaScriptCode();

            GeneralUtility::writeFileToTypo3tempDir(GeneralUtility::getFileAbsFileName($filePath), $javaScriptCode);
        }

        $this->assetHandlerConnectorManager->getPageRenderer()->addJsFooterFile($filePath);

        return $this;
    }

    /**
     * Will include all new JavaScript files given, by checking that every given
     * file was not already included.
     *
     * @param array $javaScriptValidationFiles List of JavaScript validation files.
     */
    protected function includeJavaScriptValidationFiles(array $javaScriptValidationFiles)
    {
        $javaScriptValidationFiles = array_unique($javaScriptValidationFiles);
        $assetHandlerConnectorStates = $this->assetHandlerConnectorManager->getAssetHandlerConnectorStates();

        foreach ($javaScriptValidationFiles as $file) {
            if (false === in_array($file, $assetHandlerConnectorStates->getAlreadyIncludedValidationJavaScriptFiles())) {
                $path = Core::get()->getResourceRelativePath($file);
                $this->assetHandlerConnectorManager->getPageRenderer()->addJsFooterFile($path);
                $assetHandlerConnectorStates->registerIncludedValidationJavaScriptFiles($file);
            }
        }
    }
}
