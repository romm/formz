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

use Romm\Formz\Service\Traits\ExtendedSelfInstantiateTrait;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Service\EnvironmentService;

class StringService implements SingletonInterface
{
    use ExtendedSelfInstantiateTrait;

    /**
     * @var EnvironmentService
     */
    protected $environmentService;

    /**
     * @param string $path If a string is given, it will be precessed by the extension relative path and returned.
     * @return string
     */
    public function getExtensionRelativePath($path)
    {
        $relativePath = ExtensionService::get()->getExtensionRelativePath();

        if ($this->environmentService->isEnvironmentInBackendMode()) {
            $relativePath = '../' . $relativePath;
        }

        return (null !== $path)
            ? $relativePath . $path
            : $relativePath;
    }

    /**
     * @param string $path
     * @return string
     */
    public function getResourceRelativePath($path)
    {
        $relativePath = rtrim(
            PathUtility::getRelativePath(
                GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT'),
                GeneralUtility::getFileAbsFileName($path)
            ),
            '/'
        );

        if ($this->environmentService->isEnvironmentInBackendMode()) {
            $relativePath = '../' . $relativePath;
        }

        return $relativePath;
    }

    /**
     * Sanitizes a string: lower case with dash separation.
     *
     * @param string $string
     * @return string
     */
    public function sanitizeString($string)
    {
        $string = str_replace('_', '-', GeneralUtility::camelCaseToLowerCaseUnderscored($string));

        while (strpos($string, '--')) {
            $string = str_replace('--', '-', $string);
        }

        return $string;
    }

    /**
     * @param EnvironmentService $environmentService
     */
    public function injectEnvironmentService(EnvironmentService $environmentService)
    {
        $this->environmentService = $environmentService;
    }
}
