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

use Romm\ConfigurationObject\ConfigurationObjectInterface;
use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Configuration\ConfigurationFactory;
use Romm\Formz\Core\Core;
use Romm\Formz\Exceptions\ClassNotFoundException;
use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Exceptions\InvalidArgumentTypeException;
use Romm\Formz\Exceptions\InvalidArgumentValueException;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObject\Builder\DefaultFormObjectBuilder;
use Romm\Formz\Form\FormObject\Builder\FormObjectBuilderInterface;
use Romm\Formz\Form\FormObject\Service\FormObjectSteps;
use Romm\Formz\Service\CacheService;
use Romm\Formz\Service\ContextService;
use Romm\Formz\Service\StringService;
use Romm\Formz\Service\Traits\ExtendedSelfInstantiateTrait;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Factory class that will manage form object instances.
 *
 * You can fetch a new instance by calling one of the following methods:
 * `getInstanceFromFormInstance()` or `getInstanceFromClassName()`
 *
 * @see \Romm\Formz\Form\FormObject\FormObject
 */
class FormObjectFactory implements SingletonInterface
{
    use ExtendedSelfInstantiateTrait;

    /**
     * @var FormObject[]
     */
    protected $instances = [];

    /**
     * @var FormObjectStatic[]
     */
    protected $static = [];

    /**
     * @var FormObjectProxy[]
     */
    protected $proxy = [];

    /**
     * @var FormObjectSteps[]
     */
    protected $stepService = [];

    /**
     * Returns the form object for the given form instance. The form instance
     * must have been defined first in this factory, or an exception is thrown.
     *
     * @param FormInterface $form
     * @return FormObject
     * @throws EntryNotFoundException
     */
    public function getInstanceWithFormInstance(FormInterface $form)
    {
        if (false === $this->formInstanceWasRegistered($form)) {
            throw EntryNotFoundException::formObjectInstanceNotFound($form);
        }

        return $this->instances[spl_object_hash($form)];
    }

    /**
     * Checks that the given form instance was registered in this factory.
     *
     * @param FormInterface $form
     * @return bool
     */
    public function formInstanceWasRegistered(FormInterface $form)
    {
        return isset($this->instances[spl_object_hash($form)]);
    }

    /**
     * Registers a new form instance.
     *
     * @param FormInterface $form
     * @param string        $name
     * @throws DuplicateEntryException
     * @throws InvalidArgumentValueException
     */
    public function registerFormInstance(FormInterface $form, $name)
    {
        if (empty($name)) {
            throw InvalidArgumentValueException::formNameEmpty($form);
        }

        if ($this->formInstanceWasRegistered($form)) {
            throw DuplicateEntryException::formObjectInstanceAlreadyRegistered($form, $name);
        }

        $hash = spl_object_hash($form);
        $this->instances[$hash] = $this->getInstanceWithClassName(get_class($form), $name);
        $this->instances[$hash]->setForm($form);
    }

    /**
     * A shortcut function to register the given form instance (if it was not
     * already registered) and return the form object.
     *
     * @param FormInterface $form
     * @param string        $name
     * @return FormObject
     */
    public function registerAndGetFormInstance(FormInterface $form, $name)
    {
        if (false === $this->formInstanceWasRegistered($form)) {
            $this->registerFormInstance($form, $name);
        }

        return $this->getInstanceWithFormInstance($form);
    }

    /**
     * Will create an instance of `FormObject` based on a class that implements
     * the interface `FormInterface`.
     *
     * @param string $className
     * @param string $name
     * @return FormObject
     */
    public function getInstanceWithClassName($className, $name)
    {
        /** @var FormObject $formObject */
        $formObject = Core::instantiate(FormObject::class, $name, $this->getStaticInstance($className));

        return $formObject;
    }

    /**
     * Returns the proxy object for the given form object and form instance.
     *
     * Please use with caution, as this is a very low level function!
     *
     * @param FormInterface $form
     * @return FormObjectProxy
     */
    public function getProxy(FormInterface $form)
    {
        $hash = spl_object_hash($form);

        if (false === isset($this->proxy[$hash])) {
            $this->proxy[$hash] = $this->getNewProxyInstance($form);
        }

        return $this->proxy[$hash];
    }

    /**
     * @todo
     *
     * @param FormObject $formObject
     * @return FormObjectSteps
     */
    public function getStepService(FormObject $formObject)
    {
        $hash = $formObject->getObjectHash();

        if (false === isset($this->stepService[$hash])) {
            $this->stepService[$hash] = Core::instantiate(FormObjectSteps::class, $formObject);
        }

        return $this->stepService[$hash];
    }

    /**
     * @param string $className
     * @return FormObjectStatic
     * @throws ClassNotFoundException
     * @throws InvalidArgumentTypeException
     */
    protected function getStaticInstance($className)
    {
        if (false === class_exists($className)) {
            throw ClassNotFoundException::wrongFormClassName($className);
        }

        if (false === in_array(FormInterface::class, class_implements($className))) {
            throw InvalidArgumentTypeException::wrongFormType($className);
        }

        $cacheIdentifier = $this->getCacheIdentifier($className);

        if (false === isset($this->static[$cacheIdentifier])) {
            $cacheInstance = $this->getCacheInstance();

            if ($cacheInstance->has($cacheIdentifier)) {
                $static = $cacheInstance->get($cacheIdentifier);
            } else {
                $static = $this->buildStaticInstance($className);
                $static->getObjectHash();

                if (false === $static->getDefinitionValidationResult()->hasErrors()) {
                    $cacheInstance->set($cacheIdentifier, $static);
                }
            }

            $static->getDefinition()->attachParent($this->getRootConfiguration());

            // The definition is frozen: no modification will be allowed after that.
            $static->getDefinition()->getState()->markAsFrozen();

            $this->static[$cacheIdentifier] = $static;
        }

        return $this->static[$cacheIdentifier];
    }

    /**
     * @param string $className
     * @return string
     */
    protected function getCacheIdentifier($className)
    {
        $sanitizedClassName = StringService::get()->sanitizeString(str_replace('\\', '-', $className));
        $sanitizedClassName .= '-' . ContextService::get()->getContextHash();

        return 'form-object-' . $sanitizedClassName;
    }

    /**
     * Wrapper for unit tests.
     *
     * @param string $className
     * @return FormObjectStatic
     */
    protected function buildStaticInstance($className)
    {
        /** @var FormObjectBuilderInterface $builder */
        $builder = Core::instantiate(DefaultFormObjectBuilder::class);

        return $builder->getStaticInstance($className);
    }

    /**
     * Wrapper for unit tests.
     *
     * @param FormInterface $form
     * @return FormObjectProxy
     */
    protected function getNewProxyInstance(FormInterface $form)
    {
        $formObject = $this->getInstanceWithFormInstance($form);

        /** @var FormObjectProxy $formObjectProxy */
        $formObjectProxy = Core::instantiate(FormObjectProxy::class, $formObject, $form);

        return $formObjectProxy;
    }

    /**
     * @return Configuration|ConfigurationObjectInterface
     */
    protected function getRootConfiguration()
    {
        return ConfigurationFactory::get()->getRootConfiguration()->getObject(true);
    }

    /**
     * @return FrontendInterface
     */
    protected function getCacheInstance()
    {
        return CacheService::get()->getCacheInstance();
    }
}
