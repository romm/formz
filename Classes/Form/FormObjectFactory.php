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

use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Configuration\ConfigurationFactory;
use Romm\Formz\Core\Core;
use Romm\Formz\Exceptions\ClassNotFoundException;
use Romm\Formz\Exceptions\InvalidArgumentTypeException;
use Romm\Formz\Service\CacheService;
use Romm\Formz\Service\HashService;
use Romm\Formz\Service\TypoScriptService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;

/**
 * Factory class which will manage instances of `FormObject`.
 */
class FormObjectFactory implements SingletonInterface
{
    const IGNORE_PROPERTY = 'formz-ignore';

    /**
     * @var ConfigurationFactory
     */
    protected $configurationFactory;

    /**
     * @var TypoScriptService
     */
    protected $typoScriptService;

    /**
     * @var FormObject[]
     */
    protected $instances = [];

    /**
     * @var array
     */
    private static $ignoredProperties = ['validationData', 'uid', 'pid', '_localizedUid', '_languageUid', '_versionedUid'];

    /**
     * Will create an instance of `FormObject` based on a class which implements
     * the interface `FormInterface`.
     *
     * @param string $className
     * @param string $name
     * @return FormObject
     * @throws ClassNotFoundException
     * @throws InvalidArgumentTypeException
     */
    public function getInstanceFromClassName($className, $name)
    {
        if (false === class_exists($className)) {
            throw ClassNotFoundException::wrongFormClassName($className);
        }

        if (false === in_array(FormInterface::class, class_implements($className))) {
            throw InvalidArgumentTypeException::wrongFormType($className);
        }

        $cacheIdentifier = $this->getCacheIdentifier($className, $name);

        if (false === isset($this->instances[$cacheIdentifier])) {
            $cacheInstance = CacheService::get()->getCacheInstance();

            if ($cacheInstance->has($cacheIdentifier)) {
                $instance = $cacheInstance->get($cacheIdentifier);
            } else {
                $instance = $this->createInstance($className, $name);
                $cacheInstance->set($cacheIdentifier, $instance);
            }

            /** @var Configuration $formzConfigurationObject */
            $formzConfigurationObject = $this->configurationFactory
                ->getFormzConfiguration()
                ->getObject(true);

            if (false === $formzConfigurationObject->hasForm($instance->getClassName(), $instance->getName())) {
                $formzConfigurationObject->addForm($instance);
            }

            $this->instances[$cacheIdentifier] = $instance;
        }

        return $this->instances[$cacheIdentifier];
    }

    /**
     * @param FormInterface $form
     * @param string        $name
     * @return FormObject
     */
    public function getInstanceFromFormInstance(FormInterface $form, $name)
    {
        $formObject = $this->getInstanceFromClassName(get_class($form), $name);

        return $formObject;
    }

    /**
     * Creates and initializes a new `FormObject` instance.
     *
     * @param string $className
     * @param string $name
     * @return FormObject
     */
    protected function createInstance($className, $name)
    {
        $formConfiguration = $this->typoScriptService->getFormConfiguration($className);

        /** @var FormObject $instance */
        $instance = Core::instantiate(FormObject::class, $className, $name, $formConfiguration);

        $this->insertObjectProperties($instance);

        $instance->getHash();

        return $instance;
    }

    /**
     * Will insert all the accessible properties of the given instance.
     *
     * @param FormObject $instance
     */
    protected function insertObjectProperties(FormObject $instance)
    {
        $className = $instance->getClassName();

        /** @var ReflectionService $reflectionService */
        $reflectionService = GeneralUtility::makeInstance(ReflectionService::class);
        $reflectionProperties = $reflectionService->getClassPropertyNames($className);

        $classReflection = new \ReflectionClass($className);
        $publicProperties = $classReflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($reflectionProperties as $property) {
            if (false === in_array($property, self::$ignoredProperties)
                && false === $reflectionService->isPropertyTaggedWith($className, $property, self::IGNORE_PROPERTY)
                && ((true === in_array($property, $publicProperties))
                    || $reflectionService->hasMethod($className, 'get' . ucfirst($property))
                )
            ) {
                $instance->addProperty($property);
            }
        }

        unset($publicProperties);
    }

    /**
     * @param string $className
     * @param string $name
     * @return string
     */
    protected function getCacheIdentifier($className, $name)
    {
        return vsprintf(
            'form-object-%s-%s',
            [
                CacheService::get()->getFormCacheIdentifier($className, $name),
                HashService::get()->getHash($className)
            ]
        );
    }
    
    /**
     * @return FormObject[]
     */
    public function getInstances()
    {
        return $this->instances;
    }

    /**
     * @param ConfigurationFactory $configurationFactory
     */
    public function injectConfigurationFactory(ConfigurationFactory $configurationFactory)
    {
        $this->configurationFactory = $configurationFactory;
    }

    /**
     * @param TypoScriptService $typoScriptService
     */
    public function injectTypoScriptService(TypoScriptService $typoScriptService)
    {
        $this->typoScriptService = $typoScriptService;
    }
}
