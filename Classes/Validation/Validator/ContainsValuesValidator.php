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

class ContainsValuesValidator extends AbstractValidator
{
    const OPTION_VALUES = 'values';

    const MESSAGE_DEFAULT = 'default';
    const MESSAGE_EMPTY = 'empty';

    /**
     * @inheritdoc
     */
    protected $supportedOptions = [
        self::OPTION_VALUES => [[], 'The values that are accepted, can be a string of valued delimited by a pipe.', 'array', true]
    ];

    /**
     * @inheritdoc
     */
    protected $supportedMessages = [
        self::MESSAGE_DEFAULT => [
            'key'       => 'validator.form.contains_values.error',
            'extension' => null
        ],
        self::MESSAGE_EMPTY   => [
            'key'       => 'validator.form.required.error',
            'extension' => null
        ]
    ];

    /**
     * @inheritdoc
     */
    public function isValid($valuesArray)
    {
        if (false === is_array($valuesArray)) {
            $valuesArray = [$valuesArray];
        }

        if (empty($valuesArray)) {
            $this->addError(self::MESSAGE_EMPTY, 1487943450);
        } else {
            $flag = true;
            $values = $this->options[self::OPTION_VALUES];

            if (false === is_array($values)) {
                $values = GeneralUtility::trimExplode('|', $values);
            }

            foreach ($valuesArray as $value) {
                $flag = $flag && in_array($value, $values);
            }

            if (false === $flag) {
                $this->addError(
                    self::MESSAGE_DEFAULT,
                    1445952458,
                    [implode(', ', $values)]
                );
            }
        }
    }
}
