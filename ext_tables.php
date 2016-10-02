<?php
if (!defined('TYPO3_MODE')) {
    throw new \Exception('Access denied.');
}

/** @noinspection PhpUndefinedVariableInspection */
call_user_func(
    function ($extensionKey) {
        // Including TypoScript.
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            $extensionKey,
            'Configuration/TypoScript',
            '[Formz] Global configuration'
        );

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            $extensionKey,
            'Configuration/TypoScript/View/Bootstrap/Bootstrap3',
            '[Formz] View configuration for Twitter Bootstrap 3'
        );

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            $extensionKey,
            'Configuration/TypoScript/View/Foundation/Foundation5',
            '[Formz] View configuration for Foundation 5'
        );
    },
    $_EXTKEY
);
