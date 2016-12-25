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

namespace Romm\Formz\ViewHelpers;

use Romm\Formz\AssetHandler\Html\DataAttributesAssetHandler;
use Romm\Formz\Configuration\Form\Field\Field;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Message;
use TYPO3\CMS\Extbase\Error\Notice;
use TYPO3\CMS\Extbase\Error\Warning;
use TYPO3\CMS\Extbase\Validation\Error;

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
     * @inheritdoc
     */
    public function initializeArguments()
    {
        $this->registerArgument('message', 'string', 'The message which will be formatted.', true);
        $this->registerArgument('field', 'string', 'Name of the field which will be managed. By default, it is the field from the current `FieldViewHelper`.');
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        $message = $this->arguments['message'];
        if (false === $message instanceof Message) {
            throw new \Exception('The argument "message" for the view helper "' . __CLASS__ . '" must be an instance of "' . Message::class . '".', 1467021406);
        }

        $fieldName = $this->arguments['field'];

        if (null === $fieldName
            && null !== $this->service->getCurrentField()
        ) {
            /** @var Field $field */
            $field = $this->service->getCurrentField();
            $fieldName = $field->getFieldName();
        }

        if (null === $fieldName) {
            throw new \Exception(
                'The field could not be fetched, please either use this view helper inside the view helper "' . FieldViewHelper::class . '", or fill the parameter "field" of this view helper with the field name you want.',
                1467624152
            );
        }

        $formObject = $this->service->getFormObject();

        if (false === $formObject->getConfiguration()->hasField($fieldName)) {
            throw new \Exception(
                vsprintf(
                    'The Form "%s" does not have a field "%s"',
                    [$formObject->getName(), $fieldName]
                ),
                1473084335
            );
        }

        $field = $formObject->getConfiguration()->getField($fieldName);

        if ($message instanceof Error) {
            $messageType = 'error';
        } elseif ($message instanceof Warning) {
            $messageType = 'warning';
        } elseif ($message instanceof Notice) {
            $messageType = 'notice';
        } else {
            $messageType = 'message';
        }

        list($ruleName, $messageKey) = GeneralUtility::trimExplode(':', $message->getTitle());

        $templateVariableContainer = $this->renderingContext->getTemplateVariableContainer();
        $fieldId = ($templateVariableContainer->exists('fieldId'))
            ? $templateVariableContainer->get('fieldId')
            : DataAttributesAssetHandler::getFieldCleanName('formz-' . $formObject->getName() . '-' . $fieldName);

        $result = str_replace(
            [
                '#FIELD#',
                '#FIELD_ID#',
                '#VALIDATOR#',
                '#TYPE#',
                '#KEY#',
                '#MESSAGE#'
            ],
            [
                $fieldName,
                $fieldId,
                $ruleName,
                $messageType,
                $messageKey,
                $message->getMessage()
            ],
            $field->getSettings()->getMessageTemplate()
        );

        return $result;
    }
}
