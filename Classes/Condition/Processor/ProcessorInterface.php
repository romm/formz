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

namespace Romm\Formz\Condition\Processor;

use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Configuration\Form\Field\Validation\Validation;
use Romm\Formz\Form\FormObject;

/**
 * Interface for a processor.
 *
 * A processor will manage to handle the validation condition for fields of a
 * form configuration.
 *
 * Different types of processor can exist, and return several types of results:
 * CSS, JavaScript, PHP, etc.
 */
interface ProcessorInterface
{

    /**
     * Constructor, should contain a valid form configuration.
     *
     * @param FormObject $formObject
     */
    public function __construct(FormObject $formObject);

    /**
     * Returns the final result of a field activation for the processor, after
     * the nodes have all been processed.
     *
     * @param Field $field Field instance.
     * @return mixed
     */
    public function getFieldActivationConditionTree(Field $field);

    /**
     * Returns the final result of a the given field validation rule activation
     * for the processor, after the nodes have all been processed.
     *
     * @param Field      $field      Field instance.
     * @param Validation $validation Validation instance.
     * @return mixed
     */
    public function getFieldValidationActivationConditionTree(Field $field, Validation $validation);
}
