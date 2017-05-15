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

use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Configuration\View\Layouts\LayoutGroup;
use Romm\Formz\Configuration\View\View;
use Romm\Formz\Form\Definition\Condition\Activation;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\Definition\Field\Validation\Validator;
use Romm\Formz\Form\Definition\FormDefinition;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Form\FormObject\FormObjectStatic;

class DuplicateEntryException extends FormzException
{
    const DUPLICATED_FORM_CONTEXT = 'You can not use a form view helper inside another one.';

    const FORM_WAS_ALREADY_REGISTERED = 'The form "%s" was already registered. You can only register a form once. Check the function `%s::hasForm()`.';

    const FORM_INSTANCE_ALREADY_ADDED = 'The form instance was already added for the form object of class "%s". You cannot add it twice.';

    const FORM_OBJECT_INSTANCE_ALREADY_REGISTERED = 'The form instance of type "%s" (name "%s") was already registered in the form object factory.';

    const FIELD_ALREADY_ADDED = 'The field "%s" already exists in the form definition. Please use the function `%s::hasField()` before.';

    const FORM_CONDITION_ALREADY_ADDED = 'The condition "%s" already exists in the form definition. Please use the function `%s::hasCondition()` before.';

    const ACTIVATION_CONDITION_ALREADY_ADDED = 'The condition "%s" already exists for the activation. Please use the function `%s::hasCondition()` before.';

    const FIELD_VALIDATOR_ALREADY_ADDED = 'The validator "%s" already exists for the field "%s". Please use the function `%s::hasValidator()` before.';

    const FIELD_BEHAVIOUR_ALREADY_ADDED = 'The behaviour "%s" already exists for the field "%s". Please use the function `%s::hasBehaviour()` before.';

    const VALIDATOR_MESSAGE_ALREADY_ADDED = 'The message "%s" already exists for the validator "%s". Please use the function `%s::hasMessage()` before.';

    const VIEW_LAYOUT_ALREADY_ADDED = 'The layout "%s" already exists for the view. Please use the function `%s::hasLayout()` before.';

    const LAYOUT_ITEM_ALREADY_ADDED = 'The item "%s" already exists for the layout "%s". Please use the function `%s::hasItem()` before.';

    /**
     * @code 1465242575
     *
     * @return self
     */
    final public static function duplicatedFormContext()
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(self::DUPLICATED_FORM_CONTEXT);

        return $exception;
    }

    /**
     * @code 1477255145
     *
     * @param FormObjectStatic $form
     * @return self
     */
    final public static function formWasAlreadyRegistered(FormObjectStatic $form)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FORM_WAS_ALREADY_REGISTERED,
            [$form->getClassName(), Configuration::class]
        );

        return $exception;
    }

    /**
     * @code 1491898212
     *
     * @param FormObject $formObject
     * @return self
     */
    final public static function formInstanceAlreadyAdded(FormObject $formObject)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FORM_INSTANCE_ALREADY_ADDED,
            [$formObject->getClassName()]
        );

        return $exception;
    }

    /**
     * @code 1494515318
     *
     * @param FormInterface $form
     * @param string        $name
     * @return self
     */
    final public static function formObjectInstanceAlreadyRegistered(FormInterface $form, $name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FORM_OBJECT_INSTANCE_ALREADY_REGISTERED,
            [get_class($form), $name]
        );

        return $exception;
    }

    /**
     * @code 1493881202
     *
     * @param string $name
     * @return self
     */
    final public static function fieldAlreadyAdded($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_ALREADY_ADDED,
            [$name, FormDefinition::class]
        );

        return $exception;
    }

    /**
     * @code 1493882348
     *
     * @param string $name
     * @return self
     */
    final public static function formConditionAlreadyAdded($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FORM_CONDITION_ALREADY_ADDED,
            [$name, FormDefinition::class]
        );

        return $exception;
    }

    /**
     * @code 1494329265
     *
     * @param string $name
     * @return self
     */
    final public static function activationConditionAlreadyAdded($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::ACTIVATION_CONDITION_ALREADY_ADDED,
            [$name, Activation::class]
        );

        return $exception;
    }

    /**
     * @code 1494685038
     *
     * @param string $validatorName
     * @param string $fieldName
     * @return self
     */
    final public static function fieldValidatorAlreadyAdded($validatorName, $fieldName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_VALIDATOR_ALREADY_ADDED,
            [$validatorName, $fieldName, Field::class]
        );

        return $exception;
    }

    /**
     * @code 1494685575
     *
     * @param string $behaviourName
     * @param Field  $field
     * @return self
     */
    final public static function fieldBehaviourAlreadyAdded($behaviourName, Field $field)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_BEHAVIOUR_ALREADY_ADDED,
            [$behaviourName, $field->getName(), Field::class]
        );

        return $exception;
    }

    /**
     * @code 1494694566
     *
     * @param string    $messageName
     * @param Validator $validator
     * @return self
     */
    final public static function validatorMessageAlreadyAdded($messageName, Validator $validator)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::VALIDATOR_MESSAGE_ALREADY_ADDED,
            [$messageName, $validator->getName(), Validator::class]
        );

        return $exception;
    }

    /**
     * @code 1494846335
     *
     * @param string $name
     * @return self
     */
    final public static function viewLayoutAlreadyAdded($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::VIEW_LAYOUT_ALREADY_ADDED,
            [$name, View::class]
        );

        return $exception;
    }

    /**
     * @code 1494846944
     *
     * @param string      $name
     * @param LayoutGroup $layoutGroup
     * @return self
     */
    final public static function layoutItemAlreadyAdded($name, LayoutGroup $layoutGroup)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::LAYOUT_ITEM_ALREADY_ADDED,
            [$name, $layoutGroup->getName(), LayoutGroup::class]
        );

        return $exception;
    }
}
