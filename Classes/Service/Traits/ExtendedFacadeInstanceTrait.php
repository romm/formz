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

namespace Romm\Formz\Service\Traits;

use Romm\Formz\Service\FacadeService;

/**
 * Same as `FacadeInstanceTrait`, but the instance is created with Extbase
 * object manager.
 *
 * @internal This trait is for FormZ internal usage only: it may change at any moment, so do not use it in your own scripts!
 */
trait ExtendedFacadeInstanceTrait
{
    /**
     * @return self
     */
    public static function get()
    {
        return FacadeService::get()->getInstance(self::class, true);
    }
}
