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

use TYPO3\CMS\Core\Utility\GeneralUtility;

class EmailValidator extends AbstractValidator
{
    const MESSAGE_DEFAULT = 'default';

    /**
     * @inheritdoc
     */
    protected static $javaScriptValidationFiles = [
        'EXT:formz/Resources/Public/JavaScript/Validators/Formz.Validator.Email.js'
    ];

    /**
     * @inheritdoc
     */
    protected $supportedMessages = [
        self::MESSAGE_DEFAULT => [
            'key'       => 'validator.form.email.error',
            'extension' => null
        ]
    ];

    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        if (false === GeneralUtility::validEmail((string)$value)) {
            $this->addError(self::MESSAGE_DEFAULT, 1447333632);
        }
    }
}
