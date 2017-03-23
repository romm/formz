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

use Romm\Formz\Exceptions\EntryNotFoundException;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

class EqualsToFieldValidator extends AbstractValidator
{
    const OPTION_FIELD = 'field';

    const MESSAGE_DEFAULT = 'default';

    /**
     * @inheritdoc
     */
    protected $supportedOptions = [
        self::OPTION_FIELD => [
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
        self::MESSAGE_DEFAULT => [
            'key'       => 'validator.form.equals_to_field.error',
            'extension' => null
        ]
    ];

    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        $fieldName = $this->options[self::OPTION_FIELD];
        $formObject = $this->dataObject->getFormObject();

        if (false === $formObject->hasProperty($fieldName)) {
            throw EntryNotFoundException::equalsToFieldValidatorFieldNotFound($fieldName, $formObject);
        }

        $fieldValue = ObjectAccess::getProperty($this->form, $fieldName);

        if ($value !== $fieldValue) {
            $this->addError(
                self::MESSAGE_DEFAULT,
                1446026489,
                [$fieldName]
            );
        }
    }
}
