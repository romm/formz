<?php
/*
 * 2017 Romain CANON <romain.hydrocanon@gmail.com>
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

use TYPO3\CMS\Core\Utility\MathUtility;

class BetweenNumbersValidator extends AbstractValidator
{
    const OPTION_MINIMUM = 'minimum';
    const OPTION_MAXIMUM = 'maximum';

    const MESSAGE_DEFAULT = 'default';
    const MESSAGE_NOT_NUMBER = 'notNumber';

    /**
     * @inheritdoc
     */
    protected static $javaScriptValidationFiles = [
        'EXT:formz/Resources/Public/JavaScript/Validators/Formz.Validator.BetweenNumbers.js'
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
        self::MESSAGE_DEFAULT    => [
            'key'       => 'validator.form.between_numbers.error',
            'extension' => null
        ],
        self::MESSAGE_NOT_NUMBER => [
            'key'       => 'validator.form.number.error',
            'extension' => null
        ]
    ];

    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        if (false === MathUtility::canBeInterpretedAsFloat($value)) {
            $this->addError(self::MESSAGE_NOT_NUMBER, 1462883487);
        } elseif ($value < $this->options['minimum']
            || $value > $this->options['maximum']
        ) {
            $this->addError(
                self::MESSAGE_DEFAULT,
                1462884916,
                [$this->options['minimum'], $this->options['maximum']]
            );
        }
    }
}
