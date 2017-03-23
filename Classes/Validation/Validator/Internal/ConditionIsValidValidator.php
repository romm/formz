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

namespace Romm\Formz\Validation\Validator\Internal;

use Romm\Formz\Condition\Parser\ConditionParser;
use Romm\Formz\Configuration\Form\Field\Activation\ActivationInterface;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class ConditionIsValidValidator extends AbstractValidator
{
    const ERROR_CODE = 1457621104;

    /**
     * Checks if the value is a valid condition string.
     *
     * @param ActivationInterface $condition The condition instance that should be validated.
     */
    public function isValid($condition)
    {
        $conditionTree = ConditionParser::get()
            ->parse($condition);

        if (true === $conditionTree->getValidationResult()->hasErrors()) {
            $errorMessage = $this->translateErrorMessage(
                'validator.form.condition_is_valid.error',
                'formz',
                [
                    $condition->getExpression(),
                    $conditionTree->getValidationResult()->getFirstError()->getMessage()
                ]
            );

            $this->addError($errorMessage, self::ERROR_CODE);
        }
    }
}
