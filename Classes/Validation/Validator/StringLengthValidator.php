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

class StringLengthValidator extends AbstractValidator
{

    /**
     * @inheritdoc
     */
    protected static $javaScriptValidationFiles = [
        'EXT:formz/Resources/Public/JavaScript/Validators/Formz.Validator.StringLength.js'
    ];

    /**
     * @inheritdoc
     */
    protected $supportedOptions = [
        'minimum' => [0, 'The minimum length to accept', 'integer'],
        'maximum' => [PHP_INT_MAX, 'The maximum length to accept', 'integer'],
    ];

    /**
     * @inheritdoc
     */
    protected $supportedMessages = [
        'default' => [
            'key'       => 'validator.form.string_length.error',
            'extension' => null
        ]
    ];

    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        if (strlen($value) < $this->options['minimum'] || strlen($value) > $this->options['maximum']) {
            $this->addError(
                'default',
                1445862696,
                [$this->options['minimum'], $this->options['maximum']]
            );
        }
    }
}
