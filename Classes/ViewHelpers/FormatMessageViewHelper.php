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

namespace Romm\Formz\ViewHelpers;

use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Error\FormzMessageInterface;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Exceptions\InvalidArgumentTypeException;
use Romm\Formz\Service\StringService;
use Romm\Formz\Service\ViewHelper\FieldViewHelperService;
use Romm\Formz\Service\ViewHelper\FormViewHelperService;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Message;
use TYPO3\CMS\Extbase\Error\Notice;
use TYPO3\CMS\Extbase\Error\Warning;

/**
 * This view helper can format the validation result messages of a field.
 *
 * It will use the message template defined for the given field, and handle
 * every dynamic value which can be found in the template (see below):
 *
 * #FIELD# : Name of the field;
 * #FIELD_ID# : Value of the `id` attribute of the field DOM element;
 * #VALIDATOR# : Name of the validation rule;
 * #TYPE#' : Type of the message (usually `error`);
 * #KEY#' : Key of the message (usually `default`);
 * #MESSAGE# : The message itself.
 */
class FormatMessageViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @var FormViewHelperService
     */
    protected $formService;

    /**
     * @var FieldViewHelperService
     */
    protected $fieldService;

    /**
     * @inheritdoc
     */
    public function initializeArguments()
    {
        $this->registerArgument('message', 'object', 'The message which will be formatted.', true);
        $this->registerArgument('field', 'string', 'Name of the field which will be managed. By default, it is the field from the current `FieldViewHelper`.');
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        $message = $this->getMessage();
        $fieldName = $this->getFieldName();
        $field = $this->getField();
        $formObject = $this->formService->getFormObject();

        $variableProvider = $this->getVariableProvider();

        $fieldId = ($variableProvider->exists('fieldId'))
            ? $variableProvider->get('fieldId')
            : StringService::get()->sanitizeString('formz-' . $formObject->getName() . '-' . $fieldName);

        $result = str_replace(
            [
                '#FIELD#',
                '#FIELD_ID#',
                '#TYPE#',
                '#VALIDATOR#',
                '#KEY#',
                '#MESSAGE#'
            ],
            [
                $fieldName,
                $fieldId,
                $this->getMessageType($message),
                $message->getValidationName(),
                $message->getMessageKey(),
                $message->getMessage()
            ],
            $field->getSettings()->getMessageTemplate()
        );

        return $result;
    }

    /**
     * @return Message|FormzMessageInterface
     * @throws InvalidArgumentTypeException
     */
    protected function getMessage()
    {
        $message = $this->arguments['message'];

        if (false === $message instanceof FormzMessageInterface) {
            throw InvalidArgumentTypeException::formatMessageViewHelperMessageInvalidType($message);
        }

        return $message;
    }

    /**
     * @param FormzMessageInterface $message
     * @return string
     */
    protected function getMessageType(FormzMessageInterface $message)
    {
        if ($message instanceof Error) {
            $messageType = 'error';
        } elseif ($message instanceof Warning) {
            $messageType = 'warning';
        } elseif ($message instanceof Notice) {
            $messageType = 'notice';
        } else {
            $messageType = 'message';
        }

        return $messageType;
    }

    /**
     * @return string
     * @throws EntryNotFoundException
     */
    protected function getFieldName()
    {
        $fieldName = $this->arguments['field'];

        if (empty($fieldName)
            && $this->fieldService->fieldContextExists()
        ) {
            $field = $this->fieldService->getCurrentField();
            $fieldName = $field->getFieldName();
        }

        if (null === $fieldName) {
            throw EntryNotFoundException::formatMessageViewHelperFieldNotFound($fieldName);
        }

        return $fieldName;
    }

    /**
     * @return Field
     * @throws EntryNotFoundException
     */
    protected function getField()
    {
        $formObject = $this->formService->getFormObject();
        $fieldName = $this->getFieldName();

        if (false === $formObject->getConfiguration()->hasField($fieldName)) {
            throw EntryNotFoundException::formatMessageViewHelperFieldNotFoundInForm($fieldName, $formObject);
        }

        return $formObject->getConfiguration()->getField($fieldName);
    }

    /**
     * @param FormViewHelperService $service
     */
    public function injectFormService(FormViewHelperService $service)
    {
        $this->formService = $service;
    }

    /**
     * @param FieldViewHelperService $service
     */
    public function injectFieldService(FieldViewHelperService $service)
    {
        $this->fieldService = $service;
    }
}
