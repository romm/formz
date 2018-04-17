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

class RequiredValidator extends AbstractValidator
{
    const OPTION_CHECK_BOOL = 'checkBool';

    const MESSAGE_DEFAULT = 'default';

    /**
     * @inheritdoc
     */
    protected static $javaScriptValidationFiles = [
        'EXT:formz/Resources/Public/JavaScript/Validators/Formz.Validator.Required.js'
    ];

    /**
     * @inheritdoc
     */
    protected $acceptsEmptyValues = false;

    /**
     * @inheritdoc
     */
    protected $supportedOptions = [
        self::OPTION_CHECK_BOOL => [
            false,
            'Should the validator check for boolean values (useful for checkbox values)? By default it wont.',
            'bool'
        ]
    ];

    /**
     * @inheritdoc
     */
    protected $supportedMessages = [
        self::MESSAGE_DEFAULT => [
            'key'       => 'validator.form.required.error',
            'extension' => null
        ]
    ];

    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        if (null === $value
            || '' === $value
            || (is_array($value) && empty($value))
            || (is_object($value) && $value instanceof \Countable && $value->count() === 0)
            || $this->options[self::OPTION_CHECK_BOOL] && $value === false
        ) {
            $this->addError(self::MESSAGE_DEFAULT, 1446026582);
        }
    }
}
