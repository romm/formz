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

use Romm\Formz\Condition\ConditionParser;
use Romm\Formz\Configuration\Form\Field\Activation\Activation;
use Romm\Formz\Validation\Validator\AbstractValidator;

class ConditionIsValidValidator extends AbstractValidator
{

    /**
     * @inheritdoc
     */
    protected $supportedMessages = [
        'default' => [
            'key'       => 'validator.form.condition_is_valid.error',
            'extension' => null
        ]
    ];

    /**
     * Checks if the value is a valid condition string.
     *
     * @param Activation $condition The condition instance that should be validated.
     */
    public function isValid($condition)
    {
        $conditionParser = ConditionParser::parse($condition);

        if (true === $conditionParser->getResult()->hasErrors()) {
            $this->addError(
                'default',
                1457621104,
                [
                    $condition->getCondition(),
                    $conditionParser->getResult()->getFirstError()->getMessage()
                ]
            );
        }
    }
}
