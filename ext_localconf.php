<?php
if (!defined('TYPO3_MODE')) {
    throw new \Exception('Access denied.');
}

/** @noinspection PhpUndefinedVariableInspection */
call_user_func(
    function () {
        // Registering the default Formz conditions.
        \Romm\Formz\Condition\ConditionFactory::get()->registerDefaultConditions();

        // Registering the cache.
        if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Romm\Formz\Service\CacheService::CACHE_IDENTIFIER])) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Romm\Formz\Service\CacheService::CACHE_IDENTIFIER] = [
                'backend'  => \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class,
                'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
                'groups'   => ['all', 'system', 'pages'],
            ];
        }

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] = \Romm\Formz\Core\Core::class . '->clearCacheCommand';

        $typo3Version = \TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version();

        if (version_compare($typo3Version, '8.3.0', '<')) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\Romm\Formz\ViewHelpers\FormViewHelper::class] = [
                'className' => \Romm\Formz\ViewHelpers\Legacy\FormViewHelper::class
            ];
        }
    }
);
