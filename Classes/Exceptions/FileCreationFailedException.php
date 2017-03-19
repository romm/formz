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

namespace Romm\Formz\Exceptions;

class FileCreationFailedException extends FormzException
{
    const FILE_CREATION_FAILED = 'The file "%s" could not be created, error message is: "%s".';

    /**
     * @code 1489955763
     *
     * @param string $absolutePath
     * @param string $message
     * @return FileCreationFailedException
     */
    final public static function fileCreationFailed($absolutePath, $message)
    {
        /** @var self $exception */
        $exception = self::getNewExceptionInstance(
            self::FILE_CREATION_FAILED,
            [$absolutePath, $message]
        );

        return $exception;
    }
}
