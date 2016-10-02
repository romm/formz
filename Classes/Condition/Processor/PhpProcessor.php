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

use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObject;
use Romm\Formz\Validation\Validator\Form\AbstractFormValidator;

/**
 * PHP implementation of the condition processors.
 */
class PhpProcessor extends AbstractProcessor
{

    /**
     * @var FormInterface
     */
    protected $formInstance;

    /**
     * @var AbstractFormValidator
     */
    protected $formValidator;

    /**
     * @inheritdoc
     *
     * @var bool
     */
    protected static $storeInCache = false;

    /**
     * Constructor, should contain a valid form configuration.
     *
     * @param FormObject            $formObject
     * @param FormInterface         $formInstance
     * @param AbstractFormValidator $formValidator
     * @throws \Exception
     */
    public function __construct(FormObject $formObject, FormInterface $formInstance = null, AbstractFormValidator $formValidator = null)
    {
        if (null === $formInstance) {
            throw new \Exception('A PHP processor must be constructed with a form instance.', 1458318769);
        }
        if (null === $formValidator) {
            throw new \Exception('A PHP processor must be constructed with a form validator instance.', 1458320981);
        }

        $this->formInstance = $formInstance;
        $this->formValidator = $formValidator;

        parent::__construct($formObject);
    }

    /**
     * @return FormInterface
     */
    public function getFormInstance()
    {
        return $this->formInstance;
    }

    /**
     * @return AbstractFormValidator
     */
    public function getFormValidator()
    {
        return $this->formValidator;
    }

}
