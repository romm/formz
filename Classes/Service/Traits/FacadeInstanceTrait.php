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

namespace Romm\Formz\Service\Traits;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This trait provides a static function to help getting a singleton instance of
 * a service.
 *
 * @internal This trait is for Formz internal usage only: it may change at any moment, so do not use it in your own scripts!
 */
trait FacadeInstanceTrait
{
    /**
     * @var FacadeInstanceTrait
     */
    private static $facadeInstance;

    /**
     * @return self
     */
    public static function get()
    {
        if (null === self::$facadeInstance) {
            self::$facadeInstance = GeneralUtility::makeInstance(self::class);
        }

        return self::$facadeInstance;
    }
}
