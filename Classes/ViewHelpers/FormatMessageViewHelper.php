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

namespace Romm\Formz\ViewHelpers;

use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Error\FormzMessageInterface;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Exceptions\InvalidArgumentTypeException;
use Romm\Formz\Exceptions\InvalidEntryException;
use Romm\Formz\Service\MessageService;
use Romm\Formz\Service\StringService;
use Romm\Formz\ViewHelpers\Service\FieldService;
use Romm\Formz\ViewHelpers\Service\FormService;
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
     * @var FormService
     */
    protected $formService;

    /**
     * @var FieldService
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
                MessageService::get()->getMessageValidationName($message),
                MessageService::get()->getMessageKey($message),
                $message->getMessage()
            ],
            $field->getSettings()->getMessageTemplate()
        );

        return $result;
    }

    /**
     * @return Message|FormzMessageInterface
     * @throws \Exception
     */
    protected function getMessage()
    {
        $message = $this->arguments['message'];

        if (false === $message instanceof Message) {
            throw new InvalidArgumentTypeException(
                'The argument "message" for the view helper "' . __CLASS__ . '" must be an instance of "' . Message::class . '".',
                1467021406
            );
        }

        return $message;
    }

    /**
     * @param Message $message
     * @return string
     */
    protected function getMessageType(Message $message)
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
     * @throws \Exception
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
            throw new InvalidEntryException(
                'The field could not be fetched, please either use this view helper inside the view helper "' . FieldViewHelper::class . '", or fill the parameter "field" of this view helper with the field name you want.',
                1467624152
            );
        }

        return $fieldName;
    }

    /**
     * @return Field
     * @throws \Exception
     */
    protected function getField()
    {
        $formObject = $this->formService->getFormObject();
        $fieldName = $this->getFieldName();

        if (false === $formObject->getConfiguration()->hasField($fieldName)) {
            throw new EntryNotFoundException(
                vsprintf(
                    'The Form "%s" does not have a field "%s"',
                    [$formObject->getName(), $fieldName]
                ),
                1473084335
            );
        }

        return $formObject->getConfiguration()->getField($fieldName);
    }

    /**
     * @param FormService $service
     */
    public function injectFormService(FormService $service)
    {
        $this->formService = $service;
    }

    /**
     * @param FieldService $service
     */
    public function injectFieldService(FieldService $service)
    {
        $this->fieldService = $service;
    }
}
