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

namespace Romm\Formz\AssetHandler\Html;

use DateTime;
use Romm\Formz\AssetHandler\AbstractAssetHandler;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Error\FormzMessageInterface;
use Romm\Formz\Exceptions\InvalidArgumentTypeException;
use Romm\Formz\Service\MessageService;
use Romm\Formz\Service\StringService;
use Throwable;
use Traversable;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Reflection\Exception\PropertyNotAccessibleException;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * This asset handler generates several data attributes which will be added to
 * the form element in the Fluid template. Most of these data attributes are
 * directly bound to fields and their properties.
 *
 * Example of data attributes:
 *  - Fields values: when a field changes, its new value will be indicated in
 *    the form with the attribute: `fz-value-{field name}="value"`.
 *  - Fields validation: when a field is considered as valid (it passed all its
 *    validation rules), the form gets the attribute: `fz-valid-{field name}`.
 *  - Fields errors: when a field validation fails with an error, the form gets
 *    the attribute: `fz-error-{field name}-{name of the error}`.
 *  - Fields warnings and notices: same as errors.
 */
class DataAttributesAssetHandler extends AbstractAssetHandler
{
    /**
     * Handles the data attributes containing the values of the form fields.
     *
     * Example: `fz-value-color="blue"`
     *
     * @param FormResult $formResult
     * @return array
     */
    public function getFieldsValuesDataAttributes(FormResult $formResult)
    {
        $result = [];
        $formObject = $this->getFormObject();
        $formInstance = $formObject->getForm();

        foreach ($formObject->getDefinition()->getFields() as $field) {
            $fieldName = $field->getName();

            if (false === $formResult->fieldIsDeactivated($field)) {
                try {
                    $value = ObjectAccess::getProperty($formInstance, $fieldName);
                } catch (PropertyNotAccessibleException $exception) {
                    continue;
                }

                try {
                    $value = $this->formatValue($value);
                } catch (Throwable $e) {
                    throw InvalidArgumentTypeException::dataAttributeValueNotFormattable($formInstance, $fieldName, $value);
                }

                if (false === empty($value)) {
                    $result[self::getFieldDataValueKey($fieldName)] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getFieldSubmissionDoneDataAttribute()
    {
        return [self::getFieldSubmissionDone() => '1'];
    }

    /**
     * Handles the data attributes for the fields which are valid.
     *
     * Example: `fz-valid-email="1"`
     *
     * @return array
     */
    public function getFieldsValidDataAttributes()
    {
        $result = [];
        $formConfiguration = $this->getFormObject()->getDefinition();
        $formResult = $this->getFormObject()->getFormResult();

        foreach ($formConfiguration->getFields() as $field) {
            $fieldName = $field->getName();

            if (false === $formResult->fieldIsOutOfScope($field)
                && false === $formResult->fieldIsDeactivated($field)
                && false === $formResult->forProperty($fieldName)->hasErrors()
            ) {
                $result[self::getFieldDataValidKey($fieldName)] = '1';
            }
        }

        return $result;
    }

    /**
     * Handles the data attributes for the fields which got errors, warnings and
     * notices.
     *
     * Examples:
     * - `fz-error-email="1"`
     * - `fz-error-email-rule-default="1"`
     *
     * @return array
     */
    public function getFieldsMessagesDataAttributes()
    {
        $result = [];
        $formConfiguration = $this->getFormObject()->getDefinition();
        $formResult = $this->getFormObject()->getFormResult();

        foreach ($formResult->getSubResults() as $fieldName => $fieldResult) {
            $field = $formConfiguration->getField($fieldName);

            if (true === $formConfiguration->hasField($fieldName)
                && false === $formResult->fieldIsOutOfScope($field)
                && false === $formResult->fieldIsDeactivated($field)
            ) {
                $result += $this->getFieldErrorMessages($fieldName, $fieldResult);
                $result += $this->getFieldWarningMessages($fieldName, $fieldResult);
                $result += $this->getFieldNoticeMessages($fieldName, $fieldResult);
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getCurrentStepDataAttribute()
    {
        $stepIdentifier = $this->getFormObject()->getCurrentStepDefinition()->getStep()->getIdentifier();

        return ['fz-current-step' => $stepIdentifier];
    }

    /**
     * @param string $fieldName
     * @param Result $fieldResult
     * @return array
     */
    protected function getFieldErrorMessages($fieldName, Result $fieldResult)
    {
        return (true === $fieldResult->hasErrors())
            ? $this->addFieldMessageDataAttribute($fieldName, $fieldResult->getErrors(), 'error')
            : [];
    }

    /**
     * @param string $fieldName
     * @param Result $fieldResult
     * @return array
     */
    protected function getFieldWarningMessages($fieldName, Result $fieldResult)
    {
        return (true === $fieldResult->hasWarnings())
            ? $this->addFieldMessageDataAttribute($fieldName, $fieldResult->getWarnings(), 'warning')
            : [];
    }

    /**
     * @param string $fieldName
     * @param Result $fieldResult
     * @return array
     */
    protected function getFieldNoticeMessages($fieldName, Result $fieldResult)
    {
        return (true === $fieldResult->hasNotices())
            ? $this->addFieldMessageDataAttribute($fieldName, $fieldResult->getNotices(), 'notice')
            : [];
    }

    /**
     * @param string                  $fieldName
     * @param FormzMessageInterface[] $messages
     * @param string                  $type
     * @return array
     */
    protected function addFieldMessageDataAttribute($fieldName, array $messages, $type)
    {
        $result = [self::getFieldDataMessageKey($fieldName, $type) => '1'];

        foreach ($messages as $message) {
            $validationName = MessageService::get()->getMessageValidationName($message);
            $messageKey = MessageService::get()->getMessageKey($message);

            $result[self::getFieldDataValidationMessageKey($fieldName, $type, $validationName, $messageKey)] = '1';
        }

        return $result;
    }

    /**
     * Checks the type of a given data attribute and formats it if needed.
     *
     * @param mixed $value
     * @return array
     */
    protected function formatValue($value)
    {
        if (is_array($value) || $value instanceof Traversable) {
            $value = implode(',', $value);
        } elseif ($value instanceof DateTime) {
            $format = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] . ' ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'];
            $value = $value->format($format);
        } elseif (false === is_string($value)) {
            $value = (string)$value;
        }

        return $value;
    }

    /**
     * Formats the data value attribute key for a given field name.
     *
     * @param string $fieldName Name of the field.
     * @return string
     */
    public static function getFieldDataValueKey($fieldName)
    {
        return 'fz-value-' . StringService::get()->sanitizeString($fieldName);
    }

    /**
     * Formats the data valid attribute key for a given field name.
     *
     * @param string $fieldName Name of the field.
     * @return string
     */
    public static function getFieldDataValidKey($fieldName)
    {
        return 'fz-valid-' . StringService::get()->sanitizeString($fieldName);
    }

    /**
     * Formats the data message attribute key for a given field name.
     *
     * @param string $fieldName Name of the field.
     * @param string $type      Type of the message: `error`, `warning` or `notice`.
     * @return string
     */
    public static function getFieldDataMessageKey($fieldName, $type = 'error')
    {
        return 'fz-' . $type . '-' . StringService::get()->sanitizeString($fieldName);
    }

    /**
     * @return string
     */
    public static function getFieldSubmissionDone()
    {
        return 'fz-submission-done';
    }

    /**
     * Formats the data message attribute key for a given failed validation for
     * the given field name.
     *
     * @param string $fieldName
     * @param string $type Type of the message: `error`, `warning` or `notice`.
     * @param string $validationName
     * @param string $messageKey
     * @return string
     */
    public static function getFieldDataValidationMessageKey($fieldName, $type, $validationName, $messageKey)
    {
        $stringService = StringService::get();

        return vsprintf(
            'fz-%s-%s-%s-%s',
            [
                $type,
                $stringService->sanitizeString($fieldName),
                $stringService->sanitizeString($validationName),
                $stringService->sanitizeString($messageKey)
            ]
        );
    }
}
