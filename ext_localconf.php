<?php
if (!defined('TYPO3_MODE')) {
    throw new \Exception('Access denied.');
}

/** @noinspection PhpUndefinedVariableInspection */
call_user_func(
    function ($extensionKey) {
        // Registering the default Formz conditions.
        \Romm\Formz\Condition\ConditionFactory::get()->registerDefaultConditions();

        // Registering the cache.
        if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Romm\Formz\Service\CacheService::CACHE_IDENTIFIER])) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Romm\Formz\Service\CacheService::CACHE_IDENTIFIER] = [
                'backend'  => \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class,
                'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
                'groups'   => ['all', 'system', 'pages']
            ];
        }

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Romm.' . $extensionKey,
            'AjaxValidation',
            [
                'AjaxValidation' => 'run'
            ],
            [
                'AjaxValidation' => 'run'
            ]
        );

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] = \Romm\Formz\Service\CacheService::class . '->clearCacheCommand';

        /** @var \TYPO3\CMS\Extbase\Object\Container\Container $container */
        $container = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\Container\Container::class);
        $typo3Version = \TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version();

        if (version_compare($typo3Version, '8.3.0', '<')) {
            $container->registerImplementation(\Romm\Formz\ViewHelpers\FormViewHelper::class, \Romm\Formz\Service\ViewHelper\Legacy\OldFormViewHelper::class);
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\Romm\Formz\ViewHelpers\FormViewHelper::class] = [
                'className' => \Romm\Formz\Service\ViewHelper\Legacy\OldFormViewHelper::class
            ];
        } else {
            $container->registerImplementation(\Romm\Formz\ViewHelpers\FormViewHelper::class, \Romm\Formz\Service\ViewHelper\Legacy\FormViewHelper::class);
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\Romm\Formz\ViewHelpers\FormViewHelper::class] = [
                'className' => \Romm\Formz\Service\ViewHelper\Legacy\FormViewHelper::class
            ];
        }

        if (version_compare($typo3Version, '7.3.0', '<')) {
            $container->registerImplementation(\Romm\Formz\ViewHelpers\Slot\HasViewHelper::class, \Romm\Formz\Service\ViewHelper\Legacy\OldHasViewHelper::class);
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\Romm\Formz\ViewHelpers\Slot\HasViewHelper::class] = [
                'className' => \Romm\Formz\Service\ViewHelper\Legacy\OldHasViewHelper::class
            ];
        }
    },
    $_EXTKEY
);
