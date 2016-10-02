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

namespace Romm\Formz\Condition\Node;

use Romm\Formz\Condition\Processor\PhpProcessor;
use Romm\Formz\Condition\Items\AbstractConditionItem;
use Romm\Formz\Configuration\Form\Field\Field;

/**
 * A condition node, which contains an instance of `AbstractConditionItem`.
 */
class ConditionNode extends AbstractNode
{

    /**
     * @var string
     */
    protected $conditionName;

    /**
     * @var AbstractConditionItem
     */
    protected $condition;

    /**
     * Contains a list of all condition classes which where used since the
     * function `distinctUsedConditions()` was called.
     *
     * @var array
     */
    protected static $distinctUsedConditions = [];

    /**
     * Constructor, which needs a name for the condition and an instance of a
     * condition item.
     *
     * @param string                $conditionName Name of the condition.
     * @param AbstractConditionItem $condition     Instance of the condition item.
     */
    public function __construct($conditionName, AbstractConditionItem $condition)
    {
        $this->conditionName = $conditionName;
        $this->condition = $condition;

        self::$distinctUsedConditions[get_class($condition)] = true;
    }

    /**
     * @inheritdoc
     */
    public function getCssResult(Field $field)
    {
        return $this->condition->getCssResult();
    }

    /**
     * @inheritdoc
     */
    public function getJavaScriptResult(Field $field)
    {
        return $this->condition->getJavaScriptResult();
    }

    /**
     * @inheritdoc
     */
    public function getPhpResult(Field $field)
    {
        /** @var PhpProcessor $processor */
        $processor = $this->getProcessor();
        $formInstance = $processor->getFormInstance();
        $formValidator = $processor->getFormValidator();

        return $this->condition->getPhpResult($formInstance, $formValidator);
    }

    /**
     * Resets the list of distinct condition classes. Use the function
     * `getDistinctUsedConditions()` to get them.
     */
    public static function distinctUsedConditions()
    {
        self::$distinctUsedConditions = [];
    }

    /**
     * @return array
     */
    public static function getDistinctUsedConditions()
    {
        return array_keys(self::$distinctUsedConditions);
    }
}
