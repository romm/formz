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

namespace Romm\Formz\Middleware\Item\FieldValidation;

use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Middleware\Argument\Arguments;

class FieldValidationArguments extends Arguments
{
    /**
     * @param Field $field
     */
    public function __construct(Field $field)
    {
        $this->add('field', $field);
    }

    /**
     * @return Field
     */
    public function getField()
    {
        return $this->get('field')->getValue();
    }
}
