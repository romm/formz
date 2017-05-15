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

namespace Romm\Formz\Validation\DataObject;

use Romm\Formz\Form\Definition\Field\Validation\Validator;
use Romm\Formz\Form\FormObject\FormObject;

class ValidatorDataObject
{
    /**
     * @var FormObject
     */
    protected $formObject;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @param FormObject $formObject
     * @param Validator  $validator
     */
    public function __construct(FormObject $formObject, Validator $validator)
    {
        $this->formObject = $formObject;
        $this->validator = $validator;
    }

    /**
     * @return FormObject
     */
    public function getFormObject()
    {
        return $this->formObject;
    }

    /**
     * @return Validator
     */
    public function getValidator()
    {
        return $this->validator;
    }
}
