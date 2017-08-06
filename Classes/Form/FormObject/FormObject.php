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

namespace Romm\Formz\Form\FormObject;

use Romm\Formz\Error\FormResult;
use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Exceptions\PropertyNotAccessibleException;
use Romm\Formz\Form\Definition\FormDefinition;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObject\Service\FormObjectRequestData;
use TYPO3\CMS\Extbase\Error\Result;

/**
 * This is the object representation of a form. In here we can manage which
 * properties the form does have, its configuration, and more.
 */
class FormObject
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var FormObjectStatic
     */
    protected $static;

    /**
     * @var FormObjectProxy
     */
    protected $proxy;

    /**
     * You should never create a new instance of this class directly, use the
     * `FormObjectFactory->getInstanceFromClassName()` function instead.
     *
     * @param string           $name
     * @param FormObjectStatic $static
     */
    public function __construct($name, FormObjectStatic $static)
    {
        $this->name = $name;
        $this->static = $static;
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
        return $this->static->getClassName();
    }

    /**
     * @return FormDefinition
     */
    public function getDefinition()
    {
        return $this->static->getDefinition();
    }

    /**
     * @return Result
     */
    public function getDefinitionValidationResult()
    {
        return $this->static->getDefinitionValidationResult();
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->static->getProperties();
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->getProxy()->getForm();
    }

    /**
     * @return bool
     */
    public function hasForm()
    {
        return $this->proxy !== null;
    }

    /**
     * @param FormInterface $form
     * @throws DuplicateEntryException
     */
    public function setForm(FormInterface $form)
    {
        if ($this->proxy) {
            throw DuplicateEntryException::formInstanceAlreadyAdded($this);
        }

        $this->registerFormInstance($form);

        $this->proxy = $this->createProxy($form);
    }

    /**
     * @return bool
     */
    public function formWasSubmitted()
    {
        return $this->hasForm() && $this->getProxy()->formWasSubmitted();
    }

    /**
     * @return bool
     */
    public function formWasValidated()
    {
        return $this->hasForm() && $this->getProxy()->formWasValidated();
    }

    /**
     * @return FormResult
     */
    public function getFormResult()
    {
        return $this->getProxy()->getFormResult();
    }

    /**
     * @return FormObjectRequestData
     */
    public function getRequestData()
    {
        return $this->getProxy()->getRequestData();
    }

    /**
     * @return string
     */
    public function getFormHash()
    {
        return $this->getProxy()->getFormHash();
    }

    /**
     * @return string
     */
    public function getObjectHash()
    {
        return $this->static->getObjectHash();
    }

    /**
     * @param FormInterface $form
     */
    protected function registerFormInstance(FormInterface $form)
    {
        if (false === FormObjectFactory::get()->formInstanceWasRegistered($form)) {
            FormObjectFactory::get()->registerFormInstance($form, $this->getName());
        }
    }

    /**
     * @return FormObjectProxy
     * @throws PropertyNotAccessibleException
     */
    protected function getProxy()
    {
        if (null === $this->proxy) {
            throw PropertyNotAccessibleException::formInstanceNotSet();
        }

        return $this->proxy;
    }

    /**
     * Wrapper for unit tests.
     *
     * @param FormInterface $form
     * @return FormObjectProxy
     */
    protected function createProxy(FormInterface $form)
    {
        return FormObjectFactory::get()->getProxy($form);
    }
}
