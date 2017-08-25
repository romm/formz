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

use Romm\Formz\Validation\Field\AbstractFieldValidator;

class StringLengthValidator extends AbstractFieldValidator
{
    const OPTION_MINIMUM = 'minimum';
    const OPTION_MAXIMUM = 'maximum';

    const MESSAGE_DEFAULT = 'default';

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
        self::OPTION_MINIMUM => [
            0,
            'The minimum length to accept',
            'integer'
        ],
        self::OPTION_MAXIMUM => [
            PHP_INT_MAX,
            'The maximum length to accept',
            'integer'
        ]
    ];

    /**
     * @inheritdoc
     */
    protected $supportedMessages = [
        self::MESSAGE_DEFAULT => [
            'key'       => 'validator.form.string_length.error',
            'extension' => null
        ]
    ];

    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        $minimum = abs($this->options[self::OPTION_MINIMUM]);
        $maximum = abs($this->options[self::OPTION_MAXIMUM]);

        if (strlen($value) < $minimum
            || strlen($value) > $maximum
        ) {
            $this->addError(
                self::MESSAGE_DEFAULT,
                1445862696,
                [$this->options['minimum'], $this->options['maximum']]
            );
        }
    }
}
