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

namespace Romm\Formz\Validation\Validator;

use Romm\Formz\Error\Error;
use Romm\Formz\Error\Notice;
use Romm\Formz\Error\Warning;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Service\MessageService;
use Romm\Formz\Validation\DataObject\ValidatorDataObject;
use TYPO3\CMS\Extbase\Error\Result;

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
     * @var ValidatorDataObject
     */
    protected $dataObject;

    /**
     * Constructs the validator, sets validation options and messages.
     *
     * @param array               $options Options for the validator.
     * @param ValidatorDataObject $dataObject
     */
    final public function __construct(array $options = [], ValidatorDataObject $dataObject)
    {
        parent::__construct($options);

        $this->dataObject = $dataObject;
        $this->form = $dataObject->getFormObject()->getForm();
    }

    /**
     * @param mixed $value
     * @return Result
     */
    public function validate($value)
    {
        /*
         * Messages are initialized just before the validation actually runs,
         * and not in the constructor.
         *
         * This allows more flexibility for the messages initialization; for
         * instance you can dynamically build the messages list in the method
         * `initializeObject()` of your validator (and not only in the class
         * variable declaration), then the messages will be processed just
         * below.
         */
        $this->messages = MessageService::get()->filterMessages(
            $this->dataObject->getValidation()->getMessages(),
            $this->supportedMessages,
            (bool)$this->supportsAllMessages
        );

        return parent::validate($value);
    }

    /**
     * Creates a new validation error and adds it to the result.
     *
     * @param string $key
     * @param int    $code
     * @param array  $arguments
     * @param string $title
     */
    protected function addError($key, $code, array $arguments = [], $title = '')
    {
        $message = $this->addMessage(Error::class, $key, $code, $arguments, $title);
        $this->result->addError($message);
    }

    /**
     * Creates a new validation warning and adds it to the result.
     *
     * @param string $key
     * @param int    $code
     * @param array  $arguments
     * @param string $title
     */
    protected function addWarning($key, $code, array $arguments = [], $title = '')
    {
        $message = $this->addMessage(Warning::class, $key, $code, $arguments, $title);
        $this->result->addWarning($message);
    }

    /**
     * Creates a new validation notice and adds it to the result.
     *
     * @param string $key
     * @param int    $code
     * @param array  $arguments
     * @param string $title
     */
    protected function addNotice($key, $code, array $arguments = [], $title = '')
    {
        $message = $this->addMessage(Notice::class, $key, $code, $arguments, $title);
        $this->result->addNotice($message);
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
     * @param string $type
     * @param string $key
     * @param string $code
     * @param array  $arguments
     * @param string $title
     * @return mixed
     * @throws EntryNotFoundException
     */
    private function addMessage($type, $key, $code, array $arguments, $title)
    {
        if (!isset($this->messages[$key])) {
            throw EntryNotFoundException::errorKeyNotFoundForValidator($key, $this);
        }

        return new $type(
            $this->getMessage($key, $arguments),
            $code,
            $this->dataObject->getValidation()->getName(),
            $key,
            [],
            $title
        );
    }

    /**
     * This function should *always* be used when a message should be translated
     * when an error occurs in the validation process.
     *
     * @param  string $key       The key of the message, usually "default".
     * @param  array  $arguments Arguments given to the message.
     * @return string
     */
    private function getMessage($key, array $arguments = [])
    {
        return MessageService::get()->parseMessageArray($this->messages[$key], $arguments);
    }

    /**
     * @return array
     */
    public static function getJavaScriptValidationFiles()
    {
        return static::$javaScriptValidationFiles;
    }
}
