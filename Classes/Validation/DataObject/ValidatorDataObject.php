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

namespace Romm\Formz\Validation\DataObject;

use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Configuration\Form\Field\Validation\Validation;
use Romm\Formz\Form\FormInterface;

class ValidatorDataObject
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Validation
     */
    protected $validation;

    /**
     * @param FormInterface $form
     * @param Validation    $validation
     */
    public function __construct(FormInterface $form, Validation $validation)
    {
        $this->form = $form;
        $this->validation = $validation;
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return Field
     */
    public function getField()
    {
        return $this->validation->getParentField();
    }

    /**
     * @return Validation
     */
    public function getValidation()
    {
        return $this->validation;
    }
}
