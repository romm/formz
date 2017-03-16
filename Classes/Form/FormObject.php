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

namespace Romm\Formz\Form;

use Romm\Formz\Configuration\Form\Form;
use Romm\Formz\Core\Core;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Service\HashService;
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
     * @var FormInterface
     */
    protected $form;

    /**
     * @var bool
     */
    protected $formWasSubmitted = false;

    /**
     * @var FormResult
     */
    protected $formResult;

    /**
     * @var string
     */
    protected $hash;

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
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
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
     * Registers a new property for this form.
     *
     * @param string $name
     * @return $this
     */
    public function addProperty($name)
    {
        if (false === $this->hasProperty($name)) {
            $this->properties[] = $name;
            $this->resetHash();
        }

        return $this;
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
     * global FormZ configuration object, and this form configuration object.
     *
     * @return Result
     */
    public function getConfigurationValidationResult()
    {
        return $this->configuration->getConfigurationValidationResult();
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return bool
     */
    public function hasForm()
    {
        return null !== $this->form;
    }

    /**
     * @param FormInterface $form
     */
    public function setForm(FormInterface $form)
    {
        $this->form = $form;
    }

    /**
     * Will mark the form as submitted (change the result returned by the
     * function `formWasSubmitted()`).
     */
    public function markFormAsSubmitted()
    {
        $this->formWasSubmitted = true;
    }

    /**
     * Returns `true` if the form was submitted by the user.
     *
     * @return bool
     */
    public function formWasSubmitted()
    {
        return $this->formWasSubmitted;
    }

    /**
     * @return FormResult
     */
    public function getFormResult()
    {
        return $this->formResult;
    }

    /**
     * @return bool
     */
    public function hasFormResult()
    {
        return null !== $this->formResult;
    }

    /**
     * @param FormResult $formResult
     */
    public function setFormResult($formResult)
    {
        $this->formResult = $formResult;
    }

    /**
     * Returns the hash, which should be calculated only once for performance
     * concerns.
     *
     * @return string
     */
    public function getHash()
    {
        if (null === $this->hash) {
            $this->hash = $this->calculateHash();
        }

        return $this->hash;
    }

    /**
     * @param array $formConfiguration
     */
    protected function setUpConfiguration(array $formConfiguration)
    {
        $this->configuration = Core::instantiate(FormObjectConfiguration::class, $this, $formConfiguration);
    }

    /**
     * Returns the calculated hash of this class.
     *
     * @return string
     */
    protected function calculateHash()
    {
        return HashService::get()->getHash(serialize($this));
    }

    /**
     * Resets the hash, which will be calculated on next access.
     */
    protected function resetHash()
    {
        $this->hash = null;
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
}
