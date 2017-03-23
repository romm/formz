<?php
if (!defined('TYPO3_MODE')) {
    throw new \Exception('Access denied.');
}

call_user_func(
    function ($extensionKey) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            $extensionKey,
            'Configuration/TypoScript',
            '[FormZ] Global configuration'
        );

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            $extensionKey,
            'Configuration/TypoScript/View/Bootstrap/Bootstrap3',
            '[FormZ] View configuration for Twitter Bootstrap 3'
        );

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            $extensionKey,
            'Configuration/TypoScript/View/Foundation/Foundation5',
            '[FormZ] View configuration for Foundation 5'
        );
    },
    'formz'
);
