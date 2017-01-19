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

use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Core\Core;
use Romm\Formz\Exceptions\ClassNotFoundException;
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
     * @throws \Exception
     */
    public function getInstanceFromClassName($className, $name)
    {
        if (false === class_exists($className)
            || false === in_array(FormInterface::class, class_implements($className))
        ) {
            throw new ClassNotFoundException(
                'Invalid class name given: "' . $className . '"; the class must be an instance of "' . FormInterface::class . '".',
                1467191011
            );
        }

        $cacheIdentifier = Core::get()->getCacheIdentifier('form-object-', $className . '-' . $name);

        if (false === isset($this->instances[$cacheIdentifier])) {
            $cacheInstance = Core::get()->getCacheInstance();

            if ($cacheInstance->has($cacheIdentifier)) {
                $instance = $cacheInstance->get($cacheIdentifier);
            } else {
                $instance = $this->createInstance($className, $name);
                $cacheInstance->set($cacheIdentifier, $instance);
            }

            /** @var Configuration $formzConfigurationObject */
            $formzConfigurationObject = Core::get()
                ->getConfigurationFactory()
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
     * Creates and initializes a new `FormObject` instance.
     *
     * @param string $className
     * @param string $name
     * @return FormObject
     */
    protected function createInstance($className, $name)
    {
        /** @var FormObject $instance */
        $instance = GeneralUtility::makeInstance(FormObject::class, $className, $name);

        $this->insertObjectProperties($instance);

        $formConfiguration = Core::get()->getTypoScriptService()->getFormConfiguration($className);
        $instance->setConfigurationArray($formConfiguration);

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
}
