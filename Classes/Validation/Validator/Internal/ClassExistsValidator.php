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

namespace Romm\Formz\Validation\Validator\Internal;

use Romm\Formz\Validation\Validator\AbstractValidator;

class ClassExistsValidator extends AbstractValidator
{

    /**
     * @inheritdoc
     */
    protected $supportedMessages = [
        'default' => [
            'key'       => 'validator.form.class_exists.error',
            'extension' => null
        ]
    ];

    /**
     * Checks if the value is an existing class.
     *
     * @param mixed $value The value that should be validated.
     */
    public function isValid($value)
    {
        if (false === class_exists($value)) {
            $this->addError('default', 1457610971, [$value]);
        }
    }
}
