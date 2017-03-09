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

namespace Romm\Formz\Service;

use Romm\Formz\Configuration\Form\Field\Validation\Message as FormzMessage;
use Romm\Formz\Configuration\Form\Field\Validation\Validation;
use Romm\Formz\Error\FormzMessageInterface;
use Romm\Formz\Service\Traits\ExtendedFacadeInstanceTrait;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Error\Message;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

class MessageService implements SingletonInterface
{
    use ExtendedFacadeInstanceTrait;

    /**
     * @var Dispatcher
     */
    protected $signalSlotDispatcher;

    /**
     * This function will go through all errors, warnings and notices and check
     * if they are instances of `FormzMessageInterface`. If not, they are
     * converted in order to have more informations that are needed later.
     *
     * @param Result     $result
     * @param Validation $validation
     * @return Result
     */
    public function sanitizeValidatorResult(Result $result, Validation $validation)
    {
        $newResult = new Result;

        $this->sanitizeValidatorResultMessages('error', $result->getFlattenedErrors(), $newResult, $validation);
        $this->sanitizeValidatorResultMessages('warning', $result->getFlattenedWarnings(), $newResult, $validation);
        $this->sanitizeValidatorResultMessages('notice', $result->getFlattenedNotices(), $newResult, $validation);

        return $newResult;
    }

    /**
     * @param string     $type
     * @param array      $messages
     * @param Result     $newResult
     * @param Validation $validation
     */
    protected function sanitizeValidatorResultMessages($type, array $messages, Result $newResult, Validation $validation)
    {
        $addMethod = 'add' . ucfirst($type);
        $objectType = 'Romm\\Formz\\Error\\' . ucfirst($type);
        $unknownCounter = 0;

        /** @var Message[] $messagesList */
        foreach ($messages as $path => $messagesList) {
            foreach ($messagesList as $message) {
                if (false === $message instanceof FormzMessageInterface) {
                    $message = new $objectType(
                        $message->getMessage(),
                        $message->getCode(),
                        $validation->getValidationName(),
                        'unknown-' . ++$unknownCounter,
                        $message->getArguments(),
                        $message->getTitle()
                    );
                }

                if (empty($path)) {
                    $newResult->$addMethod($message);
                } else {
                    $newResult->forProperty($path)->$addMethod($message);
                }
            }
        }
    }

    /**
     * @param array $message
     * @param array $arguments
     * @return string
     */
    public function parseMessageArray(array $message, array $arguments)
    {
        $result = (isset($message['value']) && $message['value'] !== '')
            ? vsprintf($message['value'], $arguments)
            : ContextService::get()->translate($message['key'], $message['extension'], $arguments);

        list($result) = $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            'getMessage',
            [$result, $message, $arguments]
        );

        return (string)$result;
    }

    /**
     * Will return an array by considering the supported messages, and filling
     * the supported ones with the given values.
     *
     * @param FormzMessage[] $messages
     * @param array          $supportedMessages
     * @param bool           $canCreateNewMessages
     * @return array
     */
    public function filterMessages(array $messages, array $supportedMessages, $canCreateNewMessages = false)
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
            if ($message instanceof FormzMessage) {
                $message = $message->toArray();
            }

            $messagesArray[$key] = $message;
        }

        $addValueToArray($messagesArray);
        $addValueToArray($supportedMessages);

        $messagesResult = $supportedMessages;

        ArrayUtility::mergeRecursiveWithOverrule(
            $messagesResult,
            $messagesArray,
            (bool)$canCreateNewMessages
        );

        return $messagesResult;
    }

    /**
     * @param Dispatcher $signalSlotDispatcher
     */
    public function injectSignalSlotDispatcher(Dispatcher $signalSlotDispatcher)
    {
        $this->signalSlotDispatcher = $signalSlotDispatcher;
    }
}
