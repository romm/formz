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

namespace Romm\Formz\Service;

use Romm\Formz\Core\Core;
use Romm\Formz\Service\Traits\ExtendedFacadeInstanceTrait;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\EnvironmentService;

class ContextService implements SingletonInterface
{
    use ExtendedFacadeInstanceTrait;

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
            : 'be-' . Core::get()->sanitizeString(GeneralUtility::_GET('M'));
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
