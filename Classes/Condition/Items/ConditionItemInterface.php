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

namespace Romm\Formz\Condition\Items;

use Romm\Formz\Condition\Exceptions\InvalidConditionException;
use Romm\Formz\Condition\Parser\Node\ConditionNode;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use Romm\Formz\Configuration\Form\Condition\Activation\ActivationInterface;
use Romm\Formz\Form\FormObject;

interface ConditionItemInterface
{
    /**
     * @param FormObject $formObject
     * @return void
     */
    public function attachFormObject(FormObject $formObject);

    /**
     * @param ActivationInterface $activation
     * @return void
     */
    public function attachActivation(ActivationInterface $activation);

    /**
     * @param ConditionNode $conditionNode
     * @return void
     */
    public function attachConditionNode(ConditionNode $conditionNode);

    /**
     * @throws InvalidConditionException
     * @return bool
     */
    public function validateConditionConfiguration();

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
