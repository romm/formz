<?php
/** @noinspection PhpUndefinedVariableInspection */
$EM_CONF[$_EXTKEY] = [
    'title'       => 'Formz : Handler for forms',
    'version'     => '0.1.0',
    'state'       => 'beta',
    'description' => 'Handle forms very easily with provided tools: TypoScript based validation, Fluid helpers, a whole JavaScript API, and more. Use pre-defined layouts for Twitter Bootstrap and Foundation to build good-looking forms in minutes. Need to build a basic form with only two fields? Need to build a huge registration form with dozens of fields? Use Formz, it will fulfill your needs!',

    'author'       => 'Romain Canon',
    'author_email' => 'romain.hydrocanon@gmail.com',

    'category'         => 'frontend',
    'clearCacheOnLoad' => 1,

    'constraints' => [
        'depends' => [
            'typo3'                => '6.2.0-7.6.99',
            'configuration_object' => '1.2.0-1.99.99'
        ]
    ]
];
