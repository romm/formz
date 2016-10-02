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

use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContainsValuesValidator extends AbstractValidator
{

    /**
     * @inheritdoc
     */
    protected $supportedOptions = [
        'values' => [[], 'The values that are accepted, can be a string of valued delimited by a pipe.', 'array', true]
    ];

    /**
     * @inheritdoc
     */
    protected $supportedMessages = [
        'default' => [
            'key'       => 'validator.form.contains_values.error',
            'extension' => null
        ]
    ];

    /**
     * @inheritdoc
     */
    public function isValid($valuesArray)
    {
        $flag = false;
        if (false === is_array($this->options['values'])) {
            $this->options['values'] = GeneralUtility::trimExplode('|', $this->options['values']);
        }
        if (false === is_array($valuesArray)) {
            $valuesArray = [$valuesArray];
        }

        foreach ($valuesArray as $value) {
            if (in_array($value, $this->options['values'])) {
                $flag = true;
                break;
            }
        }

        if (false === $flag) {
            $this->addError(
                'default',
                1445952458,
                [implode(', ', $this->options['values'])]
            );
        }
    }
}
