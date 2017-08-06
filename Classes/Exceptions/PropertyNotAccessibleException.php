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

namespace Romm\Formz\Exceptions;

use Romm\Formz\Form\FormObject\FormObject;

class PropertyNotAccessibleException extends FormzException
{
    const FIELD_NOT_ACCESSIBLE_IN_FORM = 'The form "%s" does not have an accessible property "%s". Please be sure this property exists, and it has a proper getter to access its value.';

    const FORM_INSTANCE_NOT_SET = 'The form instance is not accessible yet. You must use proxy methods after the form instance has been injected in the form object.';

    const FORM_DEFINITION_FROZEN_METHOD = 'Trying to call the method "%s::%s()" when the form definition has been frozen. If you need to modify the form definition, you must do it in the form object builder only.';

    const ROOT_CONFIGURATION_FROZEN_METHOD = 'Trying to call the method "%s::%s()" when the root configuration has been frozen. If you need to modify the root configuration, you can use the post configuration process signal.';

    const CONFIGURATION_OBJECT_FROZEN_PROPERTY = 'The property "%s::$%s" cannot be modified this way.';

    /**
     * @code 1465243619
     *
     * @param FormObject $formObject
     * @param string     $fieldName
     * @return self
     */
    final public static function fieldViewHelperFieldNotAccessibleInForm(FormObject $formObject, $fieldName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_NOT_ACCESSIBLE_IN_FORM,
            [$formObject->getClassName(), $fieldName]
        );

        return $exception;
    }

    /**
     * @code 1491815527
     *
     * @return self
     */
    final public static function formInstanceNotSet()
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(self::FORM_INSTANCE_NOT_SET);

        return $exception;
    }

    /**
     * @code 1494839287
     *
     * @param string $className
     * @param string $methodName
     * @return self
     */
    final public static function rootConfigurationFrozenMethod($className, $methodName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::ROOT_CONFIGURATION_FROZEN_METHOD,
            [$className, $methodName]
        );

        return $exception;
    }

    /**
     * @code 1494839741
     *
     * @param string $className
     * @param string $propertyName
     * @return self
     */
    final public static function rootConfigurationFrozenProperty($className, $propertyName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::CONFIGURATION_OBJECT_FROZEN_PROPERTY,
            [$className, $propertyName]
        );

        return $exception;
    }

    /**
     * @code 1494440357
     *
     * @param string $className
     * @param string $methodName
     * @return self
     */
    final public static function formDefinitionFrozenMethod($className, $methodName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FORM_DEFINITION_FROZEN_METHOD,
            [$className, $methodName]
        );

        return $exception;
    }

    /**
     * @code 1494440395
     *
     * @param string $className
     * @param string $propertyName
     * @return self
     */
    final public static function formDefinitionFrozenProperty($className, $propertyName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::CONFIGURATION_OBJECT_FROZEN_PROPERTY,
            [$className, $propertyName]
        );

        return $exception;
    }
}
