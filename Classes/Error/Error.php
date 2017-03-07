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

namespace Romm\Formz\Error;

class Error extends \TYPO3\CMS\Extbase\Validation\Error implements FormzMessageInterface
{
    use FormzMessageTrait;

    /**
     * @param string $message
     * @param int    $code
     * @param string $validationName
     * @param string $messageKey
     * @param array  $arguments
     * @param string $title
     */
    public function __construct($message, $code, $validationName, $messageKey, array $arguments = [], $title = '')
    {
        parent::__construct($message, $code, $arguments, $title);

        $this->injectValidationData($validationName, $messageKey);
    }
}
