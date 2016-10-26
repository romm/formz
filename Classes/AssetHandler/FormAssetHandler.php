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

namespace Romm\Formz\AssetHandler;

use Romm\Formz\AssetHandler\Css\ErrorContainerDisplayCssAssetHandler;
use Romm\Formz\AssetHandler\Css\FieldsActivationCssAssetHandler;
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
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

class FormAssetHandler implements SingletonInterface
{

    /**
     * List of JavaScript files which will be included whenever this view helper
     * is used.
     *
     * @var array
     */
    protected static $javaScriptFiles = [
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
     * List of CSS files which will be included whenever this view helper is
     * used.
     *
     * @var array
     */
    protected static $cssFiles = [
        'Form.Main.css'
    ];

    /**
     * @var bool
     */
    protected $assetsIncluded = false;

    /**
     * Storage for JavaScript files which were already included. It will handle
     * multiple instance of forms in the same page, by avoiding multiple
     * inclusions of the same JavaScript files.
     *
     * @var array
     */
    protected static $alreadyIncludedValidationJavaScriptFiles = [];

    /**
     * @var PageRenderer
     */
    protected $pageRenderer;

    /**
     * @var AssetHandlerFactory
     */
    protected $assetHandlerFactory;

    /**
     * @param PageRenderer        $pageRenderer
     * @param AssetHandlerFactory $assetHandlerFactory
     * @return FormAssetHandler
     */
    public static function get(PageRenderer $pageRenderer, AssetHandlerFactory $assetHandlerFactory)
    {
        /** @var FormAssetHandler $instance */
        $instance = GeneralUtility::makeInstance(self::class);
        $instance->setPageRenderer($pageRenderer);
        $instance->setAssetHandlerFactory($assetHandlerFactory);

        return $instance;
    }

    /**
     * @param PageRenderer $pageRenderer
     */
    public function setPageRenderer(PageRenderer $pageRenderer)
    {
        $this->pageRenderer = $pageRenderer;
    }

    /**
     * @param AssetHandlerFactory $assetHandlerFactory
     */
    public function setAssetHandlerFactory(AssetHandlerFactory $assetHandlerFactory)
    {
        $this->assetHandlerFactory = $assetHandlerFactory;
    }

    /**
     * Will take care of including internal Formz JavaScript and CSS files. They
     * will be included only once, even if the view helper is used several times
     * in the same page.
     *
     * @return $this
     */
    public function includeAssets()
    {
        if (false === $this->assetsIncluded) {
            $this->assetsIncluded = true;

            if (Core::get()->isInDebugMode()) {
                self::$javaScriptFiles[] = 'Formz.Debug.js';
            }

            foreach (self::$javaScriptFiles as $file) {
                $filePath = Core::get()->getExtensionRelativePath('Resources/Public/JavaScript/' . $file);
                $this->pageRenderer->addJsFile($filePath);
            }

            foreach (self::$cssFiles as $file) {
                $filePath = Core::get()->getExtensionRelativePath('Resources/Public/StyleSheets/' . $file);
                $this->pageRenderer->addCssFile($filePath);
            }

            /** @var FormzConfigurationJavaScriptAssetHandler $formzConfigurationJavaScriptAssetHandler */
            $formzConfigurationJavaScriptAssetHandler = $this->assetHandlerFactory->getAssetHandler(FormzConfigurationJavaScriptAssetHandler::class);
            $formzConfigurationJavaScriptFileName = $formzConfigurationJavaScriptAssetHandler->getJavaScriptFileName();

            if (false === file_exists(GeneralUtility::getFileAbsFileName($formzConfigurationJavaScriptFileName))) {
                GeneralUtility::writeFileToTypo3tempDir(
                    GeneralUtility::getFileAbsFileName($formzConfigurationJavaScriptFileName),
                    $formzConfigurationJavaScriptAssetHandler->getJavaScriptCode()
                );
            }

            $this->pageRenderer->addJsFooterFile($formzConfigurationJavaScriptFileName);
        }

        return $this;
    }

    /**
     * Will take care of generating the CSS with the AssetHandlerFactory. The
     * code will be put in a `.css` file in the `typo3temp` directory.
     *
     * If the file already exists, it is included directly before the code
     * generation.
     *
     * @return $this
     */
    public function includeGeneratedCss()
    {
        $filePath = $this->getFormzGeneratedFilePath() . '.css';

        if (false === file_exists(GeneralUtility::getFileAbsFileName($filePath))) {
            /** @var ErrorContainerDisplayCssAssetHandler $errorContainerDisplayCssAssetHandler */
            $errorContainerDisplayCssAssetHandler = $this->assetHandlerFactory->getAssetHandler(ErrorContainerDisplayCssAssetHandler::class);

            /** @var FieldsActivationCssAssetHandler $fieldsActivationCssAssetHandler */
            $fieldsActivationCssAssetHandler = $this->assetHandlerFactory->getAssetHandler(FieldsActivationCssAssetHandler::class);

            $css = $errorContainerDisplayCssAssetHandler->getErrorContainerDisplayCss() . LF;
            $css .= $fieldsActivationCssAssetHandler->getFieldsActivationCss();

            GeneralUtility::writeFileToTypo3tempDir(GeneralUtility::getFileAbsFileName($filePath), $css);
        }

        $this->pageRenderer->addCssFile($filePath);

        return $this;
    }

    /**
     * Will take care of generating the JavaScript with the AssetHandlerFactory.
     * The code will be put in a `.js` file in the `typo3temp` directory.
     *
     * If the file already exists, it is included directly before the code
     * generation.
     *
     * @return $this
     */
    public function includeGeneratedJavaScript()
    {
        $filePath = $this->getFormzGeneratedFilePath() . '.js';
        $cacheInstance = Core::get()->getCacheInstance();
        $javaScriptValidationFilesCacheIdentifier = Core::get()->getCacheIdentifier('js-files-', $this->assetHandlerFactory->getFormObject()->getClassName());

        /** @var FieldsValidationJavaScriptAssetHandler $fieldValidationConfigurationAssetHandler */
        $fieldValidationConfigurationAssetHandler =  $this->assetHandlerFactory->getAssetHandler(FieldsValidationJavaScriptAssetHandler::class);

        /** @var FieldsActivationJavaScriptAssetHandler $fieldsActivationJavaScriptAssetHandler */
        $fieldsActivationJavaScriptAssetHandler = $this->assetHandlerFactory->getAssetHandler(FieldsActivationJavaScriptAssetHandler::class);

        /** @var FieldsValidationActivationJavaScriptAssetHandler $fieldsValidationActivationJavaScriptAssetHandler */
        $fieldsValidationActivationJavaScriptAssetHandler = $this->assetHandlerFactory->getAssetHandler(FieldsValidationActivationJavaScriptAssetHandler::class);

        if (false === file_exists(GeneralUtility::getFileAbsFileName($filePath))) {
            ConditionNode::distinctUsedConditions();

            /** @var FormInitializationJavaScriptAssetHandler $formInitializationJavaScriptAssetHandler */
            $formInitializationJavaScriptAssetHandler = $this->assetHandlerFactory->getAssetHandler(FormInitializationJavaScriptAssetHandler::class);

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

        $this->pageRenderer->addJsFooterFile($filePath);

        $this->includeJavaScriptValidationFiles($javaScriptFiles);

        // Here we generate the JavaScript code containing the submitted values, and the existing errors.
        /** @var FormRequestDataJavaScriptAssetHandler $formRequestDataJavaScriptAssetHandler */
        $formRequestDataJavaScriptAssetHandler = $this->assetHandlerFactory->getAssetHandler(FormRequestDataJavaScriptAssetHandler::class);

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

        $this->pageRenderer->addJsFooterInlineCode('Formz - Initialization ' . $this->assetHandlerFactory->getFormObject()->getClassName(), $javaScriptCode);

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
        $filePath = $this->getFormzGeneratedFilePath('local-' . Core::get()->getLanguageKey()) . '.js';

        if (false === file_exists(GeneralUtility::getFileAbsFileName($filePath))) {
            /** @var FormzLocalizationJavaScriptAssetHandler $formzLocalizationJavaScriptAssetHandler */
            $formzLocalizationJavaScriptAssetHandler = $this->assetHandlerFactory->getAssetHandler(FormzLocalizationJavaScriptAssetHandler::class);

            $javaScriptCode = $formzLocalizationJavaScriptAssetHandler
                ->injectTranslationsForFormFieldsValidation()
                ->getJavaScriptCode();

            GeneralUtility::writeFileToTypo3tempDir(GeneralUtility::getFileAbsFileName($filePath), $javaScriptCode);
        }

        $this->pageRenderer->addJsFooterFile($filePath);

        return $this;
    }

    /**
     * Returns a file name based on the form object class name.
     *
     * @param string $prefix
     * @return string
     */
    protected function getFormzGeneratedFilePath($prefix = '')
    {
        $formObject = $this->assetHandlerFactory->getFormObject();
        $prefix = (false === empty($prefix))
            ? $prefix . '-'
            : '';

        return Core::GENERATED_FILES_PATH . Core::get()->getCacheIdentifier('formz-' . $prefix, $formObject->getClassName() . '-' . $formObject->getName());
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

        foreach ($javaScriptValidationFiles as $file) {
            if (false === in_array($file, self::$alreadyIncludedValidationJavaScriptFiles)) {
                $path = self::getResourceRelativePath($file);
                $this->pageRenderer->addJsFooterFile($path);
                self::$alreadyIncludedValidationJavaScriptFiles[] = $file;
            }
        }
    }

    /**
     * @param string $path
     * @return string
     */
    public static function getResourceRelativePath($path)
    {
        return rtrim(
            PathUtility::getRelativePath(
                GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT'),
                GeneralUtility::getFileAbsFileName($path)
            ),
            '/'
        );
    }
}
