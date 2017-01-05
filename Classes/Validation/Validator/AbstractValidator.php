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

namespace Romm\Formz\Validation\Validator;

use Romm\Formz\AssetHandler\Html\DataAttributesAssetHandler;
use Romm\Formz\Configuration\Form\Field\Validation\Message;
use Romm\Formz\Core\Core;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Validation\Validator\Form\AbstractFormValidator;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

abstract class AbstractValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
{

    /**
     * Fill with paths to JavaScript files containing validation code. They will
     * be automatically imported when needed.
     *
     * @var array
     */
    protected static $javaScriptValidationFiles = [];

    /**
     * List of supported messages, which are used whenever an error occurs.
     * Can be overridden with TypoScript in the validator configuration.
     *
     * Example:
     * $supportedMessages = [
     *     'default'    => [
     *         'key'       => 'path.to.my.message',
     *         'extension' => 'extension_containing_message',
     *         'value'     => 'Some value' // Static value of the message, not recommended though.
     *     ]
     * ]
     *
     * @var array
     */
    protected $supportedMessages = [];

    /**
     * Set this to true if you want to be able to add any message you want.
     *
     * @var bool
     */
    protected $supportsAllMessages = false;

    /**
     * Contains the original form instance.
     *
     * @var FormInterface
     */
    protected $form;

    /**
     * Contains the name of the field which is being validated by this
     * validator.
     *
     * @var string
     */
    protected $fieldName;

    /**
     * Contains the merge of the supported messages and the TypoScript defined
     * messages.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Array of arbitral data which can be added by child classes, and will
     * then be added to the `$validationData` property of the form instance.
     *
     * @var array
     */
    protected $validationData = [];

    /**
     * @var Dispatcher
     */
    protected $signalSlotDispatcher;

    /**
     * Constructs the validator, sets validation options and messages.
     *
     * @param array         $options  Options for the validator.
     * @param FormInterface $form     The original form.
     * @param string        $field    Name of the field being validated.
     * @param Message[]     $messages Messages for the validator.
     * @throws \TYPO3\CMS\Extbase\Validation\Exception\InvalidValidationOptionsException
     */
    public function __construct(array $options = [], $form = null, $field = '', array $messages = [])
    {
        parent::__construct($options);

        $this->signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $this->form = $form;
        $this->fieldName = $field;

        $this->messages = $this->injectMessages($messages);
    }

    /**
     * Will manage the messages of the current validator: only the supported
     * messages will be handled.
     *
     * @param Message[] $messages
     * @return array
     */
    protected function injectMessages(array $messages)
    {
        // Adding the keys `value` and `extension` to the messages, only if it is missing.
        $addValueToArray = function (array &$a) {
            foreach ($a as $k => $v) {
                if (false === isset($v['value'])) {
                    $a[$k]['value'] = '';
                }
                if (false === isset($v['extension'])) {
                    $a[$k]['extension'] = '';
                }
            }

            return $a;
        };

        $messagesArray = [];
        foreach ($messages as $key => $message) {
            if (false === is_array($message)) {
                $message = $message->toArray();
            }
            $messagesArray[$key] = $message;
        }

        $addValueToArray($messagesArray);
        $addValueToArray($this->supportedMessages);

        $messagesResult = $this->supportedMessages;

        ArrayUtility::mergeRecursiveWithOverrule(
            $messagesResult,
            $messagesArray,
            (true === $this->supportsAllMessages)
        );

        return $messagesResult;
    }

    /**
     * Creates a new validation error object and adds it to `$this->errors`.
     *
     * @param string  $key       The key of the error message (from $this->messages).
     * @param int $code      The error code (a unix timestamp)
     * @param array   $arguments Arguments to be replaced in message
     * @param string  $title
     * @throws \Exception
     */
    protected function addError($key, $code, array $arguments = [], $title = '')
    {
        if (!isset($this->messages[$key])) {
            throw new \Exception('The error key "' . $key . '" does not exist for the validator "' . get_class($this) . '".', 1455272659);
        } else {
            parent::addError(
                $this->getMessage($key, $arguments),
                $code,
                [],
                DataAttributesAssetHandler::getFieldCleanName(AbstractFormValidator::getCurrentValidationName() . ':' . $key)
            );
        }
    }

    /**
     * Get the full validation data.
     *
     * @return array
     */
    public function getValidationData()
    {
        return $this->validationData;
    }

    /**
     * Refreshes entirely the validation data (see `setValidationDataValue()`).
     *
     * @param array $validationData
     */
    protected function setValidationData(array $validationData)
    {
        $this->validationData = array_merge($this->validationData, $validationData);
    }

    /**
     * Adds an arbitral value to the validator, which will be added to the
     * `$validationData` property of the form.
     *
     * @param string $key   Key of the data.
     * @param mixed  $value Value bound to the key.
     */
    protected function setValidationDataValue($key, $value)
    {
        $this->validationData[$key] = $value;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param string $messageKey
     * @return bool
     */
    protected function hasMessage($messageKey)
    {
        return true === isset($messageKey);
    }

    /**
     * This function should *always* be used when a message should be translated
     * when an error occurs in the validation process.
     *
     * You can also use `$this->getMessageFromKey()` if you want more
     * flexibility.
     *
     * @param  string $key       The key of the message, usually "default".
     * @param  array  $arguments Arguments given to the message.
     * @return string
     */
    public function getMessage($key, array $arguments = [])
    {
        $result = (isset($this->messages[$key]['value']) && $this->messages[$key]['value'] !== '')
            ? vsprintf($this->messages[$key]['value'], $arguments)
            : $this->getMessageFromKey($this->messages[$key]['key'], $this->messages[$key]['extension'], $arguments);

        list($result) = $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            'getMessage',
            [$result, $this->messages[$key], $arguments]
        );

        return $result;
    }

    /**
     * @see $this->getMessage()
     *
     * @param    string $key          Path to the message.
     * @param    string $extensionKey Extension containing the locallang reference to the message.
     * @param    array  $arguments    Arguments given to the message.
     * @return    string
     */
    protected function getMessageFromKey($key, $extensionKey = null, array $arguments = [])
    {
        return Core::get()->translate($key, $extensionKey, $arguments);
    }

    /**
     * @return array
     */
    public static function getJavaScriptValidationFiles()
    {
        return static::$javaScriptValidationFiles;
    }
}
