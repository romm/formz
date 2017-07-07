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

namespace Romm\Formz\Form\FormObject\Service;

use ReflectionClass;
use ReflectionProperty;
use Romm\Formz\Core\Core;
use Romm\Formz\Form\FormObject\FormObjectStatic;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;

class FormObjectProperties
{
    const IGNORE_PROPERTY = 'formz-ignore';

    /**
     * @var array
     */
    private static $ignoredProperties = ['uid', 'pid', '_localizedUid', '_languageUid', '_versionedUid'];

    /**
     * @var FormObjectStatic
     */
    protected $static;

    /**
     * @var array
     */
    protected $properties;

    /**
     * @var array
     */
    protected $publicProperties;

    /**
     * @var ReflectionService
     */
    protected $reflectionService;

    /**
     * @param FormObjectStatic $static
     */
    public function __construct(FormObjectStatic $static)
    {
        $this->static = $static;
        $this->reflectionService = Core::instantiate(ReflectionService::class);
    }

    /**
     * Returns all the accessible properties of the form class: the public
     * properties and the ones that can be accessed with a getter method.
     *
     * If a class property must be excluded from this list, the following tag
     * must be added to the property: `@formz-ignore`.
     *
     * @return array
     */
    public function getProperties()
    {
        if (null === $this->properties) {
            $this->properties = [];
            $reflectionProperties = $this->reflectionService->getClassPropertyNames($this->static->getClassName());

            $properties = array_filter(
                $reflectionProperties,
                function ($propertyName) {
                    return $this->propertyShouldBeAdded($propertyName);
                }
            );

            $this->properties = array_values($properties);
        }

        return $this->properties;
    }

    /**
     * @param string $propertyName
     * @return bool
     */
    protected function propertyShouldBeAdded($propertyName)
    {
        return false === in_array($propertyName, self::$ignoredProperties)
            && false === $this->reflectionService->isPropertyTaggedWith($this->static->getClassName(), $propertyName, self::IGNORE_PROPERTY)
            && $this->propertyCanBeAccessed($propertyName);
    }

    /**
     * @param string $propertyName
     * @return bool
     */
    protected function propertyCanBeAccessed($propertyName)
    {
        if (in_array($propertyName, $this->getPublicProperties())) {
            return true;
        }

        $propertyName = ucfirst($propertyName);

        $getterMethods = [
            '__call',
            'get' . $propertyName,
            'is' . $propertyName,
            'has' . $propertyName
        ];

        foreach ($getterMethods as $method) {
            if ($this->reflectionService->hasMethod($this->static->getClassName(), $method)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    protected function getPublicProperties()
    {
        if (null === $this->publicProperties) {
            $classReflection = new ReflectionClass($this->static->getClassName());
            $publicProperties = $classReflection->getProperties(ReflectionProperty::IS_PUBLIC);

            $this->publicProperties = array_map(
                function (ReflectionProperty $property) {
                    return $property->getName();
                },
                $publicProperties
            );
        }

        return $this->publicProperties;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return ['static', 'properties'];
    }

    /**
     * Injects dependencies.
     */
    public function __wakeup()
    {
        $this->reflectionService = Core::instantiate(ReflectionService::class);
    }
}
