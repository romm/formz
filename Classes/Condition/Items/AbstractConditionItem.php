<?php
/*
 * 2016 Romain CANON <romain.hydrocanon@gmail.com>
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

use Romm\ConfigurationObject\Traits\ConfigurationObject\MagicMethodsTrait;
use Romm\Formz\Core\Core;

/**
 * This class must be extended by every registered condition item. When it is
 * registered, a condition can then be used in the TypoScript configuration for
 * fields/validation activation rules.
 *
 * When you want to create a new condition item, first register it by using the
 * function `ConditionFactory::registerConditionType()` inside your
 * `ext_localconf.php`. Then, you have to implement the three abstract functions
 * of this class:
 * - `getCssResult()`
 * - `getJavaScriptResult()`
 * - `getPhpResult()`
 *
 * These functions must translate the "meaning" of this condition to the three
 * context: CSS, JavaScript and PHP.
 *
 * If you need more explanation about how this class works, please refer to the
 * documentation.
 *
 * @see \Romm\Formz\Condition\ConditionFactory
 * @see \Romm\Formz\Condition\Items\FieldHasValueCondition
 * @see \Romm\Formz\Condition\Items\FieldHasErrorCondition
 * @see \Romm\Formz\Condition\Items\FieldIsValidCondition
 */
abstract class AbstractConditionItem implements ConditionItemInterface
{
    use MagicMethodsTrait;

    /**
     * Contains a list of JavaScript files which will be included whenever this
     * condition is used.
     *
     * Example:
     * protected static $javaScriptFiles = [
     *     'EXT:formz/Resources/Public/JavaScript/Conditions/Formz.Condition.FieldHasValue.js'
     * ];
     *
     * @var array
     */
    protected static $javaScriptFiles = [];

    /**
     * Returns a generic JavaScript code which uses Formz API to validate a
     * condition which was registered in a single JavaScript file (which is
     * filled in the `$javaScriptFiles` attribute of the PHP condition class).
     *
     * @param array $data
     * @return string
     */
    protected function getDefaultJavaScriptCall(array $data)
    {
        $conditionName = addslashes(get_class($this));
        $data = Core::get()->arrayToJavaScriptJson($data);

        return <<<JS
Formz.Condition.validateCondition('$conditionName', form, $data)
JS;
    }

    /**
     * @return array
     */
    public function getJavaScriptFiles()
    {
        return static::$javaScriptFiles;
    }
}
