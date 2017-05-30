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

namespace Romm\Formz\Form;

/**
 * This interface must be implemented by forms that can be retrieved using an
 * identifier.
 *
 * By default, if the form is an instance of `DomainObjectInterface` the uid is
 * used as the form identifier. If your form uses a different identifier, use
 * this interface and implement the function `getFormIdentifier()` to return the
 * true identifier of the form.
 */
interface IdentifiableFormInterface
{
    /**
     * Must return a unique identifier used to fetch the form.
     *
     * @see IdentifiableFormInterface
     *
     * @return string|int
     */
    public function getFormIdentifier();
}
