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

namespace Romm\Formz\Form;

use Romm\Formz\Configuration\Form\Form;
use Romm\Formz\Core\Core;
use Romm\Formz\Error\FormResult;
use TYPO3\CMS\Extbase\Error\Result;

/**
 * This is the object representation of a form. In here we can manage which
 * properties the form does have, its configuration, and more.
 */
class FormObject
{
    /**
     * Name of the form.
     *
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $className;

    /**
     * The properties of the form.
     *
     * @var array
     */
    protected $properties = [];

    /**
     * @var FormObjectConfiguration
     */
    protected $configuration;

    /**
     * @var FormResult
     */
    protected $lastValidationResult;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var bool
     */
    protected $hashShouldBeCalculated = true;

    /**
     * You should never create a new instance of this class directly, use the
     * `FormObjectFactory->getInstanceFromClassName()` function instead.
     *
     * @param string $className
     * @param string $name
     * @param array  $formConfiguration
     */
    public function __construct($className, $name, array $formConfiguration)
    {
        $this->className = $className;
        $this->name = $name;
        $this->setUpConfiguration($formConfiguration);
    }

    /**
     * @param array $formConfiguration
     */
    protected function setUpConfiguration(array $formConfiguration)
    {
        $this->configuration = Core::instantiate(FormObjectConfiguration::class, $this, $formConfiguration);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Registers a new property for this form.
     *
     * @param string $name
     * @return $this
     */
    public function addProperty($name)
    {
        if (false === $this->hasProperty($name)) {
            $this->properties[] = $name;
            $this->hashShouldBeCalculated = true;
        }

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasProperty($name)
    {
        return in_array($name, $this->properties);
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return Form
     */
    public function getConfiguration()
    {
        /** @var Form $configuration */
        $configuration = $this->configuration->getConfigurationObject()->getObject(true);

        return $configuration;
    }

    /**
     * This function will merge and return the validation results of both the
     * global Formz configuration object, and this form configuration object.
     *
     * @return Result
     */
    public function getConfigurationValidationResult()
    {
        return $this->configuration->getConfigurationValidationResult();
    }

    /**
     * Returns the hash, which should be calculated only once for performance
     * concerns.
     *
     * @return string
     */
    public function getHash()
    {
        if (true === $this->hashShouldBeCalculated
            || null === $this->hash
        ) {
            $this->hashShouldBeCalculated = false;
            $this->hash = $this->calculateHash();
        }

        return $this->hash;
    }

    /**
     * Returns the calculated hash of this class.
     *
     * @return string
     */
    protected function calculateHash()
    {
        return sha1(serialize($this));
    }

    /**
     * @return FormResult
     */
    public function getLastValidationResult()
    {
        return $this->lastValidationResult;
    }

    /**
     * @return bool
     */
    public function hasLastValidationResult()
    {
        return null !== $this->lastValidationResult;
    }

    /**
     * @param FormResult $lastValidationResult
     */
    public function setLastValidationResult($lastValidationResult)
    {
        $this->lastValidationResult = $lastValidationResult;
    }

    /**
     * When this instance is saved in TYPO3 cache, we need not to store all the
     * properties to increase performance.
     *
     * @return array
     */
    public function __sleep()
    {
        return ['name', 'className', 'properties', 'hash', 'configuration'];
    }

    /**
     * When this class is unserialized, we update the flag to know if the hash
     * should be calculated or not (if it was calculated before it was
     * serialized, there is no need to calculate it again).
     */
    public function __wakeup()
    {
        $this->hashShouldBeCalculated = (null === $this->hash);
    }
}
