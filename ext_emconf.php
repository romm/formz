<?php
/** @noinspection PhpUndefinedVariableInspection */
$EM_CONF[$_EXTKEY] = [
    'title'       => 'FormZ • Modern form handler',
    'version'     => '0.4.1',
    'state'       => 'stable',
    'description' => 'Manage your forms easily with powerful tools: TypoScript based validation, Fluid view helpers, a whole JavaScript API, and more. Use pre-defined layouts for Twitter Bootstrap and Foundation to build nice-looking forms in minutes. Need to build a basic form with only two fields? Need to build a huge registration form with dozens of fields? Use FormZ, it will live up to your expectations! Visit typo3-formz.com for more information.',

    'author'       => 'Romain Canon',
    'author_email' => 'romain.hydrocanon@gmail.com',

    'category'         => 'frontend',
    'clearCacheOnLoad' => 1,

    'constraints' => [
        'depends' => [
            'typo3'                => '6.2.0-8.6.99',
            'configuration_object' => '1.6.0-1.99.99'
        ]
    ]
];
