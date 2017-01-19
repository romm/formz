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

namespace Romm\Formz\Service;

use Romm\Formz\Error\FormzMessageInterface;
use Romm\Formz\Service\Traits\FacadeInstanceTrait;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Error\Message;

class MessageService implements SingletonInterface
{
    use FacadeInstanceTrait;

    /**
     * Returns the validation name of a message: if it is an instance of
     * `FormzMessageInterface`, we can fetch it, otherwise `unknown` is
     * returned.
     *
     * @param Message $message
     * @return string
     */
    public function getMessageValidationName(Message $message)
    {
        return ($message instanceof FormzMessageInterface)
            ? $message->getValidationName()
            : 'unknown';
    }

    /**
     * Returns the key of a message: if it is an instance of
     * `FormzMessageInterface`, we can fetch it, otherwise `unknown` is
     * returned.
     *
     * @param Message $message
     * @return string
     */
    public function getMessageKey(Message $message)
    {
        return ($message instanceof FormzMessageInterface)
            ? $message->getMessageKey()
            : 'unknown';
    }
}
