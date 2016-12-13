<?php
/**
 * $ php-cs-fixer fix --config-file .php_cs
 */

if (PHP_SAPI !== 'cli') {
    die('This script supports command line usage only. Please check your command.');
}

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->exclude('Documentation')
    ->in(__DIR__);

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers([
        'remove_leading_slash_use',
        'single_array_no_trailing_comma',
        'spaces_before_semicolon',
        'unused_use',
        'concat_with_spaces',
        'whitespacy_lines',
        'ordered_use',
        'single_quote',
        'duplicate_semicolon',
        'extra_empty_lines',
        'phpdoc_no_package',
        'phpdoc_scalar',
        'no_empty_lines_after_phpdocs',
        'short_array_syntax',
        'array_element_white_space_after_comma',
        'function_typehint_space',
        'hash_to_slash_comment',
        'join_function',
        'lowercase_cast',
        'namespace_no_leading_whitespace',
        'native_function_casing',
        'no_empty_statement',
        'self_accessor',
        'short_bool_cast',
        'unneeded_control_parentheses'
    ])
    ->finder($finder);
