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

namespace Romm\Formz\Configuration\Form\Field;

use Romm\ConfigurationObject\Service\Items\Parents\ParentsTrait;
use Romm\ConfigurationObject\Traits\ConfigurationObject\StoreArrayIndexTrait;
use Romm\Formz\Configuration\AbstractFormzConfiguration;
use Romm\Formz\Configuration\Form\Condition\Activation\ActivationInterface;
use Romm\Formz\Configuration\Form\Condition\Activation\ActivationUsageInterface;
use Romm\Formz\Configuration\Form\Condition\Activation\EmptyActivation;
use Romm\Formz\Configuration\Form\Field\Behaviour\Behaviour;
use Romm\Formz\Configuration\Form\Field\Settings\FieldSettings;
use Romm\Formz\Configuration\Form\Field\Validation\Validation;
use Romm\Formz\Configuration\Form\Form;
use Romm\Formz\Exceptions\EntryNotFoundException;

class Field extends AbstractFormzConfiguration implements ActivationUsageInterface
{
    use StoreArrayIndexTrait;
    use ParentsTrait;

    /**
     * @var \ArrayObject<Romm\Formz\Configuration\Form\Field\Validation\Validation>
     */
    protected $validation = [];

    /**
     * @var \ArrayObject<Romm\Formz\Configuration\Form\Field\Behaviour\Behaviour>
     */
    protected $behaviours = [];

    /**
     * @var \Romm\Formz\Configuration\Form\Condition\Activation\ActivationResolver
     * @validate Romm.Formz:Internal\ConditionIsValid
     */
    protected $activation;

    /**
     * @var \Romm\Formz\Configuration\Form\Field\Settings\FieldSettings
     */
    protected $settings;

    /**
     * Name of the field. By default, it is the key of this field in the array
     * containing all the fields for the parent form.
     *
     * @var string
     */
    private $fieldName;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->settings = new FieldSettings();
        $this->settings->setParents([$this]);

        $this->activation = EmptyActivation::get();
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->getFirstParent(Form::class);
    }

    /**
     * @return Validation[]
     */
    public function getValidation()
    {
        return $this->validation;
    }

    /**
     * @param string $validationName
     * @return bool
     */
    public function hasValidation($validationName)
    {
        return true === isset($this->validation[$validationName]);
    }

    /**
     * @param Validation $validation
     */
    public function addValidation(Validation $validation)
    {
        $this->validation[$validation->getValidationName()] = $validation;
        $validation->setParents([$this]);
    }

    /**
     * @param $validationName
     * @return Validation
     * @throws EntryNotFoundException
     */
    public function getValidationByName($validationName)
    {
        if (false === $this->hasValidation($validationName)) {
            throw EntryNotFoundException::validationNotFound($validationName);
        }

        return $this->validation[$validationName];
    }

    /**
     * @return Behaviour[]
     */
    public function getBehaviours()
    {
        return $this->behaviours;
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
     * @param ActivationInterface $activation
     */
    public function setActivation(ActivationInterface $activation)
    {
        $activation->setRootObject($this);

        $this->activation = $activation;
    }

    /**
     * @return FieldSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        if (null === $this->fieldName) {
            $this->fieldName = $this->getArrayIndex();
        }

        return $this->fieldName;
    }

    /**
     * @param string $fieldName
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;
    }
}
