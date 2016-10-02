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

namespace Romm\Formz\Condition;

use Romm\Formz\Condition\Items\AbstractConditionItem;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Factory class for working with conditions.
 *
 * You can register a new condition by using the following code in the file
 * `ext_localconf.php` of your extension:
 *
 *  $conditionFactory = \Romm\Formz\Condition\ConditionFactory::get();
 *
 *  $conditionFactory->registerCondition(
 *      'nameOfMyCondition',
 *      \Vendor\Extension\Condition\Items\MyCondition::class
 *  );
 */
class ConditionFactory implements SingletonInterface
{

    /**
     * @var array
     */
    private $conditions = [];

    /**
     * Returns an instance of this class.
     *
     * @return ConditionFactory
     */
    public static function get()
    {
        return GeneralUtility::makeInstance(self::class);
    }

    /**
     * Use this function to register a new condition type which can then be used
     * in the TypoScript configuration. This function should be called from
     * `ext_localconf.php`.
     *
     * The name of the condition must be a valid string, which will be then be
     * used as the identifier for the TypoScript conditions. By convention, you
     * should use the following syntax: `extension_name.condition_name`.
     *
     * The condition class must extend this class (`AbstractConditionItem`) and
     * implement the abstract functions.
     *
     * @param string $conditionName  The name of the condition, which will then be available for TypoScript conditions.
     * @param string $conditionClass Class which will process the condition.
     * @throws \Exception
     */
    public function registerCondition($conditionName, $conditionClass)
    {
        if (false === is_string($conditionName)) {
            throw new \Exception('The name of the condition must be a correct string (current type: "' . gettype($conditionName) . '".', 1466588489);
        }

        if (false === class_exists($conditionClass)
            || false === is_subclass_of($conditionClass, AbstractConditionItem::class)
        ) {
            throw new \Exception('The condition class must extend "' . AbstractConditionItem::class . '" (given class is "' . $conditionClass . '").', 1466588495);
        }

        $this->conditions[$conditionName] = $conditionClass;
    }

    /**
     * @param string $conditionName
     * @return bool
     */
    public function hasCondition($conditionName)
    {
        return (true === array_key_exists($conditionName, $this->conditions));
    }

    /**
     * Returns the wanted condition. A check should be done before calling this
     * function, with `hasCondition()`.
     *
     * @param $conditionName
     * @return mixed
     * @throws \Exception
     */
    public function getCondition($conditionName)
    {
        if (false === $this->hasCondition($conditionName)) {
            throw new \Exception(
                'Trying to access a condition which is not registered: "' . $conditionName . '". Here is a list of all currently registered conditions: "' . implode('" ,"', array_keys($this->conditions)) . '".',
                1472650209
            );
        }

        return $this->conditions[$conditionName];
    }
}
