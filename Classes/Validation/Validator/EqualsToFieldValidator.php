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

class EqualsToFieldValidator extends AbstractValidator
{

    /**
     * @inheritdoc
     */
    protected $supportedOptions = [
        'field' => [
            '',
            'The field which should be equal to the current field.',
            'string',
            true
        ]
    ];

    /**
     * @inheritdoc
     */
    protected $supportedMessages = [
        'default' => [
            'key'       => 'validator.form.equals_to_field.error',
            'extension' => null
        ]
    ];

    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        $fieldGetter = 'get' . GeneralUtility::underscoredToUpperCamelCase($this->options['field']);
        $fieldValue = $this->form->$fieldGetter();
        if ($value !== $fieldValue) {
            $this->addError(
                'default',
                1446026489,
                [$this->options['field']]
            );
        }
    }
}
