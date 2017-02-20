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

namespace Romm\Formz\Configuration\Form\Field\Validation;

use Romm\ConfigurationObject\Service\Items\Parents\ParentsTrait;
use Romm\ConfigurationObject\Traits\ConfigurationObject\ArrayConversionTrait;
use Romm\ConfigurationObject\Traits\ConfigurationObject\StoreArrayIndexTrait;
use Romm\Formz\Configuration\AbstractFormzConfiguration;
use Romm\Formz\Configuration\Form\Condition\Activation\ActivationInterface;
use Romm\Formz\Configuration\Form\Condition\Activation\EmptyActivation;
use Romm\Formz\Configuration\Form\Field\Field;

class Validation extends AbstractFormzConfiguration
{
    use StoreArrayIndexTrait;
    use ArrayConversionTrait;
    use ParentsTrait;

    /**
     * @var string
     * @validate NotEmpty
     * @validate Romm.Formz:Internal\ClassExists
     */
    protected $className;

    /**
     * @var string
     * @validate Number
     */
    protected $priority;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var \ArrayObject<Romm\Formz\Configuration\Form\Field\Validation\Message>
     */
    protected $messages = [];

    /**
     * @var \Romm\Formz\Configuration\Form\Condition\Activation\ActivationResolver
     * @validate Romm.Formz:Internal\ConditionIsValid
     */
    protected $activation;

    /**
     * @var bool
     */
    protected $useAjax = false;

    /**
     * Name of the validation. By default, it is the key of this instance in the
     * array containing all the validation for the parent field.
     *
     * @var string
     */
    private $validationName;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->activation = EmptyActivation::get();
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $optionName
     * @return null|mixed
     */
    public function getOption($optionName)
    {
        return (null !== $optionName && true === isset($this->options[$optionName]))
            ? $this->options[$optionName]
            : null;
    }

    /**
     * @return \Romm\Formz\Configuration\Form\Field\Validation\Message[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return ActivationInterface
     */
    public function getActivation()
    {
        return $this->activation;
    }

    /**
     * @return bool
     */
    public function hasActivation()
    {
        return !($this->activation instanceof EmptyActivation);
    }

    /**
     * @return string
     */
    public function getValidationName()
    {
        if (null === $this->validationName) {
            $this->validationName = $this->getArrayIndex();
        }

        return $this->validationName;
    }

    /**
     * @param string $validationName
     */
    public function setValidationName($validationName)
    {
        $this->validationName = $validationName;
    }

    /**
     * @return bool
     */
    public function doesUseAjax()
    {
        return (bool) $this->useAjax;
    }

    /**
     * @return Field
     */
    public function getParentField()
    {
        return $this->getFirstParent(Field::class);
    }
}
