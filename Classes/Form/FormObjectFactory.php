<?php
/*
 * 2016 Romain CANON <romain.hydrocanon@gmail.com>
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

use Romm\Formz\Core\Core;
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
    private static $ignoredProperties = ['formConfiguration', 'validationData', 'uid', 'pid', '_localizedUid', '_languageUid', '_versionedUid'];

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
        if (false === in_array(FormInterface::class, class_implements($className))) {
            throw new \Exception(
                'Invalid class name given: "' . $className . '"; the class must be an instance of "' . FormInterface::class . '".',
                1467191011
            );
        }

        $cacheIdentifier = Core::get()->getCacheIdentifier('form-object-', $className . '-' . $name);

        if (false === isset($this->instances[$cacheIdentifier])) {
            $cacheInstance = Core::get()->getCacheInstance();

            if ($cacheInstance->has($cacheIdentifier)) {
                $this->instances[$cacheIdentifier] = $cacheInstance->get($cacheIdentifier);
            } else {
                /** @var FormObject $instance */
                $instance = GeneralUtility::makeInstance(FormObject::class, $className, $name);

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

                $formConfiguration = Core::get()->getTypoScriptUtility()->getFormConfiguration($className);
                $instance->setConfigurationArray($formConfiguration);
                $instance->getHash();

                $cacheInstance->set($cacheIdentifier, $instance);

                $this->instances[$cacheIdentifier] = $instance;
                unset($publicProperties);
            }

            Core::get()->getConfigurationFactory()
                ->addFormFromClassName($className, $name);
        }

        return $this->instances[$cacheIdentifier];
    }
}
