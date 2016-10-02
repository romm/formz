<?php
/*
 * 2016 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Formz project.
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
        'pattern' => ['', 'The pattern given to the regex.', 'string', true],
        'options' => ['', 'The options given to the regex.', 'string']
    ];

    /**
     * @inheritdoc
     */
    protected $supportedMessages = [
        'default' => [
            'key'       => 'validator.form.default_error',
            'extension' => null
        ]
    ];

    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        if (!preg_match('/' . $this->options['pattern'] . '/' . $this->options['options'], $value)) {
            $this->addError(
                'default',
                1445952446
            );
        }
    }
}
