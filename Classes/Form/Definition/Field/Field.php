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

namespace Romm\Formz\Form\Definition\Field;

use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorInterface;
use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Exceptions\SilentException;
use Romm\Formz\Form\Definition\AbstractFormDefinitionComponent;
use Romm\Formz\Form\Definition\Condition\Activation;
use Romm\Formz\Form\Definition\Condition\ActivationInterface;
use Romm\Formz\Form\Definition\Condition\ActivationUsageInterface;
use Romm\Formz\Form\Definition\Field\Behaviour\Behaviour;
use Romm\Formz\Form\Definition\Field\Settings\FieldSettings;
use Romm\Formz\Form\Definition\Field\Validation\Validator;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Field extends AbstractFormDefinitionComponent implements ActivationUsageInterface, DataPreProcessorInterface
{
    /**
     * @var string
     * @validate NotEmpty
     */
    private $name;

    /**
     * @var \Romm\Formz\Form\Definition\Field\Validation\Validator[]
     */
    protected $validation = [];

    /**
     * @var \Romm\Formz\Form\Definition\Field\Behaviour\Behaviour[]
     */
    protected $behaviours = [];

    /**
     * @var \Romm\Formz\Form\Definition\Condition\Activation
     * @validate Romm.Formz:Internal\ConditionIsValid
     */
    protected $activation;

    /**
     * @var \Romm\Formz\Form\Definition\Field\Settings\FieldSettings
     */
    private $settings;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;

        $this->settings = GeneralUtility::makeInstance(FieldSettings::class);
        $this->settings->attachParent($this);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Validator[]
     */
    public function getValidators()
    {
        return $this->validation;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasValidator($name)
    {
        return true === isset($this->validation[$name]);
    }

    /**
     * @param string $name
     * @return Validator
     * @throws EntryNotFoundException
     */
    public function getValidator($name)
    {
        if (false === $this->hasValidator($name)) {
            throw EntryNotFoundException::validatorNotFound($name);
        }

        return $this->validation[$name];
    }

    /**
     * @param string $name
     * @param string $className
     * @return Validator
     * @throws DuplicateEntryException
     */
    public function addValidator($name, $className)
    {
        $this->checkDefinitionFreezeState();

        if ($this->hasValidator($name)) {
            throw DuplicateEntryException::fieldValidatorAlreadyAdded($name, $this->getName());
        }

        /** @var Validator $validator */
        $validator = GeneralUtility::makeInstance(Validator::class, $name, $className);
        $validator->attachParent($this);

        $this->validation[$name] = $validator;

        return $validator;
    }

    /**
     * @return Behaviour[]
     */
    public function getBehaviours()
    {
        return $this->behaviours;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasBehaviour($name)
    {
        return true === isset($this->behaviours[$name]);
    }

    /**
     * @param string $name
     * @return Behaviour
     * @throws EntryNotFoundException
     */
    public function getBehaviour($name)
    {
        if (false === $this->hasBehaviour($name)) {
            throw EntryNotFoundException::behaviourNotFound($name);
        }

        return $this->behaviours[$name];
    }

    /**
     * @param string $name
     * @param string $className
     * @return Behaviour
     * @throws DuplicateEntryException
     */
    public function addBehaviour($name, $className)
    {
        $this->checkDefinitionFreezeState();

        if ($this->hasBehaviour($name)) {
            throw DuplicateEntryException::fieldBehaviourAlreadyAdded($name, $this);
        }

        /** @var Behaviour $behaviour */
        $behaviour = GeneralUtility::makeInstance(Behaviour::class, $name, $className);
        $behaviour->attachParent($this);

        $this->behaviours[$name] = $behaviour;

        return $behaviour;
    }

    /**
     * @return ActivationInterface
     * @throws SilentException
     */
    public function getActivation()
    {
        if (false === $this->hasActivation()) {
            throw SilentException::fieldHasNoActivation($this);
        }

        return $this->activation;
    }

    /**
     * @return bool
     */
    public function hasActivation()
    {
        return $this->activation instanceof ActivationInterface;
    }

    /**
     * @return ActivationInterface
     */
    public function addActivation()
    {
        $this->checkDefinitionFreezeState();

        if (null === $this->activation) {
            $this->activation = GeneralUtility::makeInstance(Activation::class);
            $this->activation->attachParent($this);
        }

        return $this->activation;
    }

    /**
     * @return FieldSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param DataPreProcessor $processor
     */
    public static function dataPreProcessor(DataPreProcessor $processor)
    {
        $data = $processor->getData();

        /*
         * Forcing the names of the validators and the behaviours: they are the
         * keys of the array entries.
         */
        self::forceNameForProperty($data, 'validation');
        self::forceNameForProperty($data, 'behaviours');

        $processor->setData($data);
    }
}
