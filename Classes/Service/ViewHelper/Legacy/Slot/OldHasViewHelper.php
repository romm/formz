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

namespace Romm\Formz\Service\ViewHelper\Legacy\Slot;

use Romm\Formz\ViewHelpers\Slot\HasViewHelper;

/**
 * Legacy version of the `HasViewHelper` for TYPO3 6.2+, used to implement the
 * function `render`.
 */
class OldHasViewHelper extends HasViewHelper
{
    /**
     * @inheritdoc
     */
    public function render()
    {
        if (static::evaluateCondition($this->arguments)) {
            return $this->renderThenChild();
        } else {
            return $this->renderElseChild();
        }
    }
}
