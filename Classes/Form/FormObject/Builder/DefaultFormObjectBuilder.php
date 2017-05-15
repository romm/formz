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

namespace Romm\Formz\Form\FormObject\Builder;

use Romm\ConfigurationObject\ConfigurationObjectFactory;
use Romm\Formz\Form\Definition\FormDefinition;
use Romm\Formz\Form\FormObject\Definition\FormDefinitionObject;
use Romm\Formz\Service\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;

/**
 * Default form object builder.
 *
 * It fetches the form configuration array from TypoScript, and automatically
 * inserts the class properties of the form in the properties of the form
 * object.
 */
class DefaultFormObjectBuilder extends AbstractFormObjectBuilder
{
    const IGNORE_PROPERTY = 'formz-ignore';

    /**
     * @var array
     */
    private static $ignoredProperties = ['validationData', 'uid', 'pid', '_localizedUid', '_languageUid', '_versionedUid'];

    /**
     * @see DefaultFormObjectBuilder
     */
    public function process()
    {
        $this->insertObjectProperties();
    }

    /**
     * @return FormDefinitionObject
     */
    protected function getFormDefinitionObject()
    {
        $configurationArray = TypoScriptService::get()->getFormConfiguration($this->className);
        $configurationObject = ConfigurationObjectFactory::convert(FormDefinition::class, $configurationArray);

        /** @var FormDefinitionObject $formDefinitionObject */
        $formDefinitionObject = GeneralUtility::makeInstance(
            FormDefinitionObject::class,
            $configurationObject->getObject(true),
            $configurationObject->getValidationResult()
        );

        return $formDefinitionObject;
    }

    /**
     * Will insert all the accessible properties of the form class in the form
     * object.
     */
    protected function insertObjectProperties()
    {
        /** @var ReflectionService $reflectionService */
        $reflectionService = GeneralUtility::makeInstance(ReflectionService::class);
        $reflectionProperties = $reflectionService->getClassPropertyNames($this->className);

        $classReflection = new \ReflectionClass($this->className);
        $publicProperties = $classReflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($reflectionProperties as $propertyName) {
            if (false === in_array($propertyName, self::$ignoredProperties)
                && false === $reflectionService->isPropertyTaggedWith($this->className, $propertyName, self::IGNORE_PROPERTY)
                && ((true === in_array($propertyName, $publicProperties))
                    || $reflectionService->hasMethod($this->className, 'get' . ucfirst($propertyName))
                )
                && false === $this->static->getDefinition()->hasField($propertyName)
            ) {
                $this->static->getDefinition()->addField($propertyName);
            }
        }

        unset($publicProperties);
    }

    /**
     * @return FormDefinition
     */
    public function getFormDefinition()
    {
        return null;
    }
}
