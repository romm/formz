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
use Romm\Formz\Configuration\View\Classes\ViewClass;
use Romm\Formz\Configuration\View\Layouts\LayoutGroup;
use Romm\Formz\Configuration\View\View;
use Romm\Formz\Domain\Model\DataObject\FormMetadataObject;
use Romm\Formz\Domain\Model\FormMetadata;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\Definition\Field\Validation\Validator;
use Romm\Formz\Form\Definition\FormDefinition;
use Romm\Formz\Form\Definition\Step\Step\StepDefinition;
use Romm\Formz\Form\Definition\Step\Steps;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Form\FormObject\Service\FormObjectRequestData;
use Romm\Formz\Persistence\Item\Session\SessionPersistence;
use Romm\Formz\Form\FormObject\FormObjectFactory;
use Romm\Formz\Validation\Validator\AbstractValidator;
use Romm\Formz\ViewHelpers\ClassViewHelper;
use Romm\Formz\ViewHelpers\FieldViewHelper;
use Romm\Formz\ViewHelpers\FormatMessageViewHelper;

class EntryNotFoundException extends FormzException
{
    const FIELD_NOT_FOUND = 'The field "%s" was not found in the form "%s" with class "%s".';

    const CONDITION_NOT_FOUND = 'Trying to access a condition which is not registered: "%s". Here is a list of all currently registered conditions: "%s".';

    const FORM_ADD_CONDITION_NOT_FOUND = 'Trying to add a condition "%s" which is not registered to the form definition. Here is a list of all currently registered conditions: "%s".';

    const ACTIVATION_ADD_CONDITION_NOT_FOUND = 'Trying to add a condition "%s" which is not registered to the activation. Here is a list of all currently registered conditions: "%s".';

    const INSTANTIATE_CONDITION_NOT_FOUND = 'Trying to instantiate a condition which is not registered: "%s". Here is a list of all currently registered conditions: "%s".';

    const ACTIVATION_CONDITION_NOT_FOUND = 'No condition "%s" was found.';

    const CONFIGURATION_FIELD_NOT_FOUND = 'The field "%s" was not found. Please use the function `%s::hasField()` before.';

    const VALIDATOR_NOT_FOUND = 'The validation "%s" was not found. Please use the function `%s::hasValidator()` before.';

    const BEHAVIOUR_NOT_FOUND = 'The behaviour "%s" was not found. Please use the function `%s::hasBehaviour()` before.';

    const MESSAGE_NOT_FOUND = 'The message "%s" was not found. Please use the function `%s::hasMessage()` before.';

    const VIEW_LAYOUT_NOT_FOUND = 'The layout "%s" was not found. Please use the function `%s::hasLayout()` before.';

    const VIEW_LAYOUT_ITEM_NOT_FOUND = 'The layout item "%s" was not found. Please use the function `%s::hasItem()` before.';

    const VIEW_CLASS_NOT_FOUND = 'The class "%s" was not found. Please use the function `%s::hasItem()` before.';

    const VALIDATION_NOT_FOUND_FOR_FIELD = 'The field "%s" does not have a rule "%s".';

    const ERROR_KEY_NOT_FOUND_FOR_VALIDATOR = 'The error key "%s" does not exist for the validator "%s".';

    const VIEW_HELPER_FIELD_NOT_FOUND = 'The field could not be fetched for the view helper "%s": please either use this view helper inside the view helper "%s", or fill the parameter `field` of this view helper with the field name you want.';

    const FIELD_VIEW_HELPER_LAYOUT_NOT_FOUND = 'The layout "%s" could not be found. Please check your TypoScript configuration.';

    const FIELD_VIEW_HELPER_LAYOUT_ITEM_NOT_FOUND = 'The layout "%s" does not have an item "%s".';

    const CONTROLLER_SERVICE_ACTION_FORM_ARGUMENT_MISSING = 'The method `%s::%s()` must have a parameter `$%s`. Note that you can also change the parameter `name` of the form view helper.';

    const SLOT_NOT_FOUND = 'No slot "%s" was found.';

    const ARGUMENT_NOT_FOUND = 'Trying to get an argument that does not exist: "%s". Please use function `has()`.';

    const FORM_REQUEST_DATA_NOT_FOUND = 'The data "%s" was not found. Please use the function `%s::hasData()` before.';

    const PERSISTENCE_SESSION_ENTRY_NOT_FOUND = 'The form with identifier "%s" was not found in the session, please use the function `%s::has()` before.';

    const META_DATA_NOT_FOUND = 'The metadata "%s" was not found. Please use the function `%s::has()` before.';

    const FORM_CONFIGURATION_NOT_FOUND = 'The configuration for form of class "%s" was not found. Please use the function `%s::hasForm()` before.';

    const CONDITION_NOT_FOUND_IN_DEFINITION = 'The condition "%s" was not found in the form definition. Please use the function `%s::hasCondition()` before.';

    const CONDITION_DOES_NOT_EXIST = 'The condition "%s" does not exist';

    const MIDDLEWARE_NOT_FOUND = 'The middleware "%s" was not found. Please use the function `%s::hasMiddleware()` before.';

    const STEP_ENTRY_NOT_FOUND = 'The step "%s" was not found. Please use the function `%s::hasEntry()` before.';

    const NEXT_STEPS_NOT_FOUND = 'The step definition for the step "%s" does not have next steps. Please use the function `%s::hasNextSteps()` before.';

    const PREVIOUS_DEFINITION_NOT_FOUND = 'The step definition for the step "%s" does not have a previous definition. Please use the function `%s::hasPreviousDefinition()` before.';

    const FORM_OBJECT_INSTANCE_NOT_FOUND = 'The form instance for the object of type "%s" was not found. Please take care of registering it before with "%s::registerFormInstance()".';

    /**
     * @param string $identifier
     * @param array  $list
     * @return self
     */
    final public static function conditionNotFound($identifier, array $list)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::CONDITION_NOT_FOUND,
            1472650209,
            [
                $identifier,
                implode('" ,"', array_keys($list))
            ]
        );

        return $exception;
    }

    /**
     * @param string $identifier
     * @param array  $list
     * @return self
     */
    final public static function formAddConditionNotFound($identifier, array $list)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FORM_ADD_CONDITION_NOT_FOUND,
            1493890438,
            [
                $identifier,
                implode('" ,"', array_keys($list))
            ]
        );

        return $exception;
    }

    /**
     * @param string $identifier
     * @param array  $list
     * @return self
     */
    final public static function activationAddConditionNotFound($identifier, array $list)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::ACTIVATION_ADD_CONDITION_NOT_FOUND,
            1494329341,
            [
                $identifier,
                implode('" ,"', array_keys($list))
            ]
        );

        return $exception;
    }

    /**
     * @param string $identifier
     * @param array  $list
     * @return self
     */
    final public static function instantiateConditionNotFound($identifier, array $list)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::INSTANTIATE_CONDITION_NOT_FOUND,
            1493890825,
            [
                $identifier,
                implode('" ,"', array_keys($list))
            ]
        );

        return $exception;
    }

    /**
     * @param string $name
     * @return self
     */
    final public static function activationConditionNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::ACTIVATION_CONDITION_NOT_FOUND,
            1488482191,
            [$name]
        );

        return $exception;
    }

    /**
     * @param string $name
     * @return self
     */
    final public static function configurationFieldNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::CONFIGURATION_FIELD_NOT_FOUND,
            1489765133,
            [$name, FormDefinition::class]
        );

        return $exception;
    }

    /**
     * @param string $name
     * @return self
     */
    final public static function validatorNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::VALIDATOR_NOT_FOUND,
            1487672276,
            [$name, Field::class]
        );

        return $exception;
    }

    /**
     * @param string $name
     * @return self
     */
    final public static function behaviourNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::BEHAVIOUR_NOT_FOUND,
            1494685753,
            [$name, Field::class]
        );

        return $exception;
    }

    /**
     * @param string $name
     * @return self
     */
    final public static function messageNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::MESSAGE_NOT_FOUND,
            1494694474,
            [$name, Validator::class]
        );

        return $exception;
    }

    /**
     * @param string $name
     * @return self
     */
    final public static function viewLayoutNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::VIEW_LAYOUT_NOT_FOUND,
            1489753952,
            [$name, View::class]
        );

        return $exception;
    }

    /**
     * @param string $name
     * @return self
     */
    final public static function viewLayoutItemNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::VIEW_LAYOUT_ITEM_NOT_FOUND,
            1489757511,
            [$name, LayoutGroup::class]
        );

        return $exception;
    }

    /**
     * @param string $name
     * @return self
     */
    final public static function viewClassNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::VIEW_CLASS_NOT_FOUND,
            1489754909,
            [$name, ViewClass::class]
        );

        return $exception;
    }

    /**
     * @param string $validationName
     * @param string $fieldName
     * @return self
     */
    final public static function ajaxControllerValidationNotFoundForField($validationName, $fieldName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::VALIDATION_NOT_FOUND_FOR_FIELD,
            1487672956,
            [$fieldName, $validationName]
        );

        return $exception;
    }

    /**
     * @param string     $fieldName
     * @param FormObject $formObject
     * @return self
     */
    final public static function ajaxControllerFieldNotFound($fieldName, FormObject $formObject)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_NOT_FOUND,
            1487671603,
            [$fieldName, $formObject->getName(), $formObject->getClassName()]
        );

        return $exception;
    }

    /**
     * @param string            $key
     * @param AbstractValidator $validator
     * @return self
     */
    final public static function errorKeyNotFoundForValidator($key, AbstractValidator $validator)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::ERROR_KEY_NOT_FOUND_FOR_VALIDATOR,
            1455272659,
            [$key, get_class($validator)]
        );

        return $exception;
    }

    /**
     * @param string     $fieldName
     * @param FormObject $formObject
     * @return self
     */
    final public static function equalsToFieldValidatorFieldNotFound($fieldName, FormObject $formObject)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_NOT_FOUND,
            1487947224,
            [$fieldName, $formObject->getName(), $formObject->getClassName()]
        );

        return $exception;
    }

    /**
     * @return self
     */
    final public static function classViewHelperFieldNotFound()
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::VIEW_HELPER_FIELD_NOT_FOUND,
            1467623761,
            [ClassViewHelper::class, FieldViewHelper::class]
        );

        return $exception;
    }

    /**
     * @param string $fieldName
     * @return self
     */
    final public static function formatMessageViewHelperFieldNotFound($fieldName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::VIEW_HELPER_FIELD_NOT_FOUND,
            1467624152,
            [$fieldName, FormatMessageViewHelper::class, FieldViewHelper::class]
        );

        return $exception;
    }

    /**
     * @param string $layoutName
     * @return self
     */
    final public static function fieldViewHelperLayoutNotFound($layoutName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_VIEW_HELPER_LAYOUT_NOT_FOUND,
            1465243586,
            [$layoutName]
        );

        return $exception;
    }

    /**
     * @param string $layoutName
     * @param string $itemName
     * @return self
     */
    final public static function fieldViewHelperLayoutItemNotFound($layoutName, $itemName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_VIEW_HELPER_LAYOUT_ITEM_NOT_FOUND,
            1485867803,
            [$layoutName, $itemName]
        );

        return $exception;
    }

    /**
     * @param string     $fieldName
     * @param FormObject $formObject
     * @return self
     */
    final public static function formatMessageViewHelperFieldNotFoundInForm($fieldName, FormObject $formObject)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FIELD_NOT_FOUND,
            1473084335,
            [$fieldName, $formObject->getName(), $formObject->getClassName()]
        );

        return $exception;
    }

    /**
     * @param string $controllerObjectName
     * @param string $actionName
     * @param string $formName
     * @return self
     */
    final public static function controllerServiceActionFormArgumentMissing($controllerObjectName, $actionName, $formName)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::CONTROLLER_SERVICE_ACTION_FORM_ARGUMENT_MISSING,
            1457441846,
            [$controllerObjectName, $actionName . 'Action', $formName]
        );

        return $exception;
    }

    /**
     * @param string $name
     * @return self
     */
    final public static function slotClosureSlotNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::SLOT_NOT_FOUND,
            1488988452,
            [$name]
        );

        return $exception;
    }

    /**
     * @param string $name
     * @return self
     */
    final public static function slotArgumentsSlotNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::SLOT_NOT_FOUND,
            1489497046,
            [$name]
        );

        return $exception;
    }

    /**
     * @param string $name
     * @return self
     */
    final public static function argumentNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::ARGUMENT_NOT_FOUND,
            1490792697,
            [$name]
        );

        return $exception;
    }

    /**
     * @param string $name
     * @return self
     */
    final public static function formRequestDataNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FORM_REQUEST_DATA_NOT_FOUND,
            1490799273,
            [$name, FormObjectRequestData::class]
        );

        return $exception;
    }

    /**
     * @param FormMetadata $metadata
     * @return self
     */
    final public static function persistenceSessionEntryNotFound(FormMetadata $metadata)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::PERSISTENCE_SESSION_ENTRY_NOT_FOUND,
            1491293933,
            [$metadata->getHash(), SessionPersistence::class]
        );

        return $exception;
    }

    /**
     * @param string $key
     * @return self
     */
    final public static function metadataNotFound($key)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::META_DATA_NOT_FOUND,
            1491814768,
            [$key, FormMetadataObject::class]
        );

        return $exception;
    }

    /**
     * @return self
     */
    final public static function formConfigurationNotFound()
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FORM_CONFIGURATION_NOT_FOUND,
            1491997168,
            [Configuration::class]
        );

        return $exception;
    }

    /**
     * @param string $name
     * @return self
     */
    final public static function conditionNotFoundInDefinition($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::CONDITION_NOT_FOUND_IN_DEFINITION,
            1493881671,
            [$name, Configuration::class]
        );

        return $exception;
    }

    /**
     * @param string $name
     * @return self
     */
    final public static function middlewareNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::MIDDLEWARE_NOT_FOUND,
            1491997309,
            [$name, FormDefinition::class]
        );

        return $exception;
    }

    /**
     * @param string $name
     * @return self
     */
    final public static function stepEntryNotFound($name)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::STEP_ENTRY_NOT_FOUND,
            1492602754,
            [$name, Steps::class]
        );

        return $exception;
    }

    /**
     * @param StepDefinition $stepDefinition
     * @return EntryNotFoundException
     */
    final public static function nextStepsNotFound(StepDefinition $stepDefinition)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::NEXT_STEPS_NOT_FOUND,
            1492603394,
            [$stepDefinition->getStep()->getIdentifier(), get_class($stepDefinition)]
        );

        return $exception;
    }

    /**
     * @param StepDefinition $stepDefinition
     * @return EntryNotFoundException
     */
    final public static function previousDefinitionNotFound(StepDefinition $stepDefinition)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::PREVIOUS_DEFINITION_NOT_FOUND,
            1492603656,
            [$stepDefinition->getStep()->getIdentifier(), get_class($stepDefinition)]
        );

        return $exception;
    }

    /**
     * @param FormInterface $form
     * @return self
     */
    final public static function formObjectInstanceNotFound(FormInterface $form)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FORM_OBJECT_INSTANCE_NOT_FOUND,
            1494514957,
            [get_class($form), FormObjectFactory::class]
        );

        return $exception;
    }
}
