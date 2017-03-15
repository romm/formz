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

use Romm\Formz\Exceptions\ClassNotFoundException;
use Romm\Formz\Exceptions\InvalidOptionValueException;
use Romm\Formz\Service\Traits\ExtendedFacadeInstanceTrait;
use TYPO3\CMS\Core\Cache\Backend\BackendInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CacheService implements SingletonInterface
{
    use ExtendedFacadeInstanceTrait;

    const CACHE_IDENTIFIER = 'cache_formz';
    const CONFIGURATION_OBJECT_CACHE_IDENTIFIER = 'cache_formz_configuration_object';
    const GENERATED_FILES_PATH = 'typo3temp/FormZ/';

    /**
     * @var TypoScriptService
     */
    protected $typoScriptService;

    /**
     * @var FrontendInterface
     */
    protected $cacheInstance;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * Returns the type of backend cache defined in TypoScript at the path:
     * `settings.defaultBackendCache`.
     *
     * @return string
     * @throws ClassNotFoundException
     * @throws InvalidOptionValueException
     */
    public function getBackendCache()
    {
        $backendCache = $this->typoScriptService->getExtensionConfigurationFromPath('settings.defaultBackendCache');

        if (false === class_exists($backendCache)) {
            throw ClassNotFoundException::backendCacheClassNameNotFound($backendCache);
        }

        if (false === in_array(BackendInterface::class, class_implements($backendCache))) {
            throw InvalidOptionValueException::wrongBackendCacheType($backendCache);
        }

        return $backendCache;
    }

    /**
     * Returns the cache instance used by this extension.
     *
     * @return FrontendInterface
     */
    public function getCacheInstance()
    {
        if (null === $this->cacheInstance) {
            $this->cacheInstance = $this->cacheManager->getCache(self::CACHE_IDENTIFIER);
        }

        return $this->cacheInstance;
    }

    /**
     * Generic cache identifier creation for usages in the extension.
     *
     * @param string $string
     * @param string $formClassName
     * @param int    $maxLength
     * @return string
     */
    public function getFormCacheIdentifier($string, $formClassName, $maxLength = 55)
    {
        $shortClassName = end(explode('\\', $formClassName));

        $identifier = strtolower(
            $string .
            $shortClassName .
            '-' .
            sha1($formClassName)
        );

        return substr($identifier, 0, $maxLength);
    }

    /**
     * Function called when clearing TYPO3 caches. It will remove the temporary
     * asset files created by FormZ.
     *
     * @param array $parameters
     */
    public function clearCacheCommand($parameters)
    {
        if (in_array($parameters['cacheCmd'], ['all', 'system'])) {
            $files = $this->getFilesInPath(self::GENERATED_FILES_PATH . '*');

            foreach ($files as $file) {
                $this->clearFile($file);
            }
        }
    }

    /**
     * @param string $path
     * @return array
     */
    protected function getFilesInPath($path)
    {
        $files = glob(GeneralUtility::getFileAbsFileName($path));

        return (false === $files)
            ? []
            : $files;
    }

    /**
     * @param string $file
     */
    protected function clearFile($file)
    {
        touch($file, 0);
    }

    /**
     * @param CacheManager $cacheManager
     */
    public function injectCacheManager(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * @param TypoScriptService $typoScriptService
     */
    public function injectTypoScriptService(TypoScriptService $typoScriptService)
    {
        $this->typoScriptService = $typoScriptService;
    }
}
