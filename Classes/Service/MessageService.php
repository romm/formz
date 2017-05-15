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

use Romm\Formz\Error\FormzMessageInterface;
use Romm\Formz\Service\Traits\ExtendedSelfInstantiateTrait;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Error\Message;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

class MessageService implements SingletonInterface
{
    use ExtendedSelfInstantiateTrait;

    /**
     * @var Dispatcher
     */
    protected $signalSlotDispatcher;

    /**
     * @param Message $message
     * @return string
     */
    public function getMessageValidationName(Message $message)
    {
        return $message instanceof FormzMessageInterface
            ? $message->getValidationName()
            : 'unknown';
    }

    /**
     * @param Message $message
     * @return string
     */
    public function getMessageKey(Message $message)
    {
        return $message instanceof FormzMessageInterface
            ? $message->getMessageKey()
            : 'unknown';
    }

    /**
     * This function will go through all errors, warnings and notices and check
     * if they are instances of `FormzMessageInterface`. If not, they are
     * converted in order to have more informations that are needed later.
     *
     * @param Result $result
     * @param string $validationName
     * @return Result
     */
    public function sanitizeValidatorResult(Result $result, $validationName)
    {
        $newResult = new Result;

        $this->sanitizeValidatorResultMessages('error', $result->getFlattenedErrors(), $newResult, $validationName);
        $this->sanitizeValidatorResultMessages('warning', $result->getFlattenedWarnings(), $newResult, $validationName);
        $this->sanitizeValidatorResultMessages('notice', $result->getFlattenedNotices(), $newResult, $validationName);

        return $newResult;
    }

    /**
     * @param string $type
     * @param array  $messages
     * @param Result $newResult
     * @param string $validationName
     */
    protected function sanitizeValidatorResultMessages($type, array $messages, Result $newResult, $validationName)
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
                        $validationName,
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
     * @param Dispatcher $signalSlotDispatcher
     */
    public function injectSignalSlotDispatcher(Dispatcher $signalSlotDispatcher)
    {
        $this->signalSlotDispatcher = $signalSlotDispatcher;
    }
}
