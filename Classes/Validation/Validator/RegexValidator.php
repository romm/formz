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

namespace Romm\Formz\Validation\Validator;

class RegexValidator extends AbstractValidator
{
    const MESSAGE_DEFAULT = 'default';

    const OPTION_PATTERN = 'pattern';
    const OPTION_OPTIONS = 'options';

    /**
     * @inheritdoc
     */
    protected static $javaScriptValidationFiles = [
        'EXT:formz/Resources/Public/JavaScript/Validators/Formz.Validator.Regex.js'
    ];

    /**
     * @inheritdoc
     */
    protected $supportedOptions = [
        self::OPTION_PATTERN => [
            '',
            'The pattern given to the regex.',
            'string',
            true
        ],
        self::OPTION_OPTIONS => [
            '',
            'The options given to the regex.',
            'string'
        ]
    ];

    /**
     * @inheritdoc
     */
    protected $supportedMessages = [
        self::MESSAGE_DEFAULT => [
            'key'       => 'validator.form.default_error',
            'extension' => null
        ]
    ];

    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        $pattern = $this->options[self::OPTION_PATTERN];
        $options = $this->options[self::OPTION_OPTIONS];
        $checking = "/$pattern/$options";
        $result = preg_match($checking, $value);

        if (false === $result
            || 0 === $result
        ) {
            $this->addError(self::MESSAGE_DEFAULT, 1445952446);
        }
    }
}
