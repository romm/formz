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

namespace Romm\Formz\Service;

use Romm\Formz\Core\Core;
use Romm\Formz\Service\Traits\ExtendedSelfInstantiateTrait;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\EnvironmentService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ContextService implements SingletonInterface
{
    use ExtendedSelfInstantiateTrait;

    /**
     * @var EnvironmentService
     */
    protected $environmentService;

    /**
     * @var TypoScriptService
     */
    protected $typoScriptService;

    /**
     * Returns a unique hash for the context of the current request, depending
     * on whether the request comes from frontend or backend.
     *
     * @return string
     */
    public function getContextHash()
    {
        return ($this->environmentService->isEnvironmentInFrontendMode())
            ? 'fe-' . Core::get()->getPageController()->id
            : 'be-' . StringService::get()->sanitizeString(GeneralUtility::_GET('M'));
    }

    /**
     * Returns the current language key.
     *
     * @return string
     */
    public function getLanguageKey()
    {
        $languageKey = 'unknown';

        if ($this->environmentService->isEnvironmentInFrontendMode()) {
            $pageController = Core::get()->getPageController();

            if (isset($pageController->config['config']['language'])) {
                $languageKey = $pageController->config['config']['language'];
            }
        } else {
            $backendUser = Core::get()->getBackendUser();

            if (strlen($backendUser->uc['lang']) > 0) {
                $languageKey = $backendUser->uc['lang'];
            }
        }

        return $languageKey;
    }

    /**
     * Translation handler. Does the same job as Extbase translation tools,
     * expect that if the index to the LLL reference is not found, the index is
     * returned (Extbase would have returned an empty string).
     *
     * @param    string $index        The index to the LLL reference.
     * @param    string $extensionKey Key of the extension containing the LLL reference.
     * @param    array  $arguments    Arguments passed over to vsprintf.
     * @return   string               The translated string.
     */
    public function translate($index, $extensionKey = null, $arguments = null)
    {
        $extensionKey = ($extensionKey) ?: ExtensionService::get()->getExtensionKey();
        $result = LocalizationUtility::translate($index, $extensionKey, $arguments);

        if (empty($result) && $index !== '') {
            $result = $index;
        }

        return $result;
    }

    /**
     * Will check if the TypoScript was actually included, as it contains
     * required configuration to make the forms work properly.
     *
     * @return bool
     */
    public function isTypoScriptIncluded()
    {
        return null !== $this->typoScriptService->getExtensionConfigurationFromPath('settings.typoScriptIncluded');
    }

    /**
     * @param EnvironmentService $environmentService
     */
    public function injectEnvironmentService(EnvironmentService $environmentService)
    {
        $this->environmentService = $environmentService;
    }

    /**
     * @param TypoScriptService $typoScriptService
     */
    public function injectTypoScriptService(TypoScriptService $typoScriptService)
    {
        $this->typoScriptService = $typoScriptService;
    }
}
