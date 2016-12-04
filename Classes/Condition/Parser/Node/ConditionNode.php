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

namespace Romm\Formz\Condition\Parser\Node;

use Romm\Formz\Condition\Items\ConditionItemInterface;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;

/**
 * A condition node, which contains an instance of `ConditionItemInterface`.
 */
class ConditionNode extends AbstractNode
{
    /**
     * @var string
     */
    protected $conditionName;

    /**
     * @var ConditionItemInterface
     */
    protected $condition;

    /**
     * Constructor, which needs a name for the condition and an instance of a
     * condition item.
     *
     * @param string                 $conditionName Name of the condition.
     * @param ConditionItemInterface $condition     Instance of the condition item.
     */
    public function __construct($conditionName, ConditionItemInterface $condition)
    {
        $this->conditionName = $conditionName;
        $this->condition = $condition;
    }

    /**
     * @inheritdoc
     */
    public function getCssResult()
    {
        return $this->toArray($this->condition->getCssResult());
    }

    /**
     * @inheritdoc
     */
    public function getJavaScriptResult()
    {
        return $this->toArray($this->condition->getJavaScriptResult());
    }

    /**
     * @inheritdoc
     */
    public function getPhpResult(PhpConditionDataObject $dataObject)
    {
        return $this->condition->getPhpResult($dataObject);
    }

    /**
     * @return ConditionItemInterface
     */
    public function getCondition()
    {
        return $this->condition;
    }
}
