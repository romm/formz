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

namespace Romm\Formz\Service\ViewHelper\Legacy;

/**
 * @see OldFormViewHelper
 */
class FormViewHelper extends \Romm\Formz\ViewHelpers\FormViewHelper
{
    /** @noinspection PhpSignatureMismatchDuringInheritanceInspection */
    /**
     * @see OldFormViewHelper
     */
    public function render()
    {
        return call_user_func_array([get_parent_class(), 'renderViewHelper'], func_get_args());
    }
}
