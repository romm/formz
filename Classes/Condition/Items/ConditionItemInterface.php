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

namespace Romm\Formz\Condition\Items;

use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;

interface ConditionItemInterface
{
    /**
     * @return string
     */
    public function getCssResult();

    /**
     * @return string
     */
    public function getJavaScriptResult();

    /**
     * @param PhpConditionDataObject $dataObject
     * @return bool
     */
    public function getPhpResult(PhpConditionDataObject $dataObject);

    /**
     * @return array
     */
    public function getJavaScriptFiles();
}
