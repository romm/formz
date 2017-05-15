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

namespace Romm\Formz\Condition\Parser\Node;

use Romm\Formz\Condition\Exceptions\InvalidConditionException;
use Romm\Formz\Condition\Items\ConditionItemInterface;
use Romm\Formz\Condition\Processor\ConditionProcessor;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use Romm\Formz\Form\Definition\Condition\ActivationInterface;
use Romm\Formz\Form\Definition\Condition\ActivationUsageInterface;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\Definition\Field\Validation\Validator;
use Romm\Formz\Form\FormObject\FormObject;

/**
 * A condition node, which contains an instance of `ConditionItemInterface`.
 */
class ConditionNode extends AbstractNode implements ActivationDependencyAwareInterface
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
     * @var FormObject
     */
    protected $formObject;

    /**
     * @var ActivationInterface
     */
    protected $activation;

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
     * @param ConditionProcessor  $processor
     * @param ActivationInterface $activation
     */
    public function injectDependencies(ConditionProcessor $processor, ActivationInterface $activation)
    {
        $this->formObject = $processor->getFormObject();
        $this->activation = $activation;

        $this->condition->attachFormObject($this->formObject);
        $this->condition->attachActivation($activation);
        $this->condition->attachConditionNode($this);
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
        $this->checkConditionConfiguration();

        return $this->condition->getPhpResult($dataObject);
    }

    /**
     * Validates the configuration of the condition instance.
     *
     * @see \Romm\Formz\Condition\Items\ConditionItemInterface::validateConditionConfiguration
     */
    protected function checkConditionConfiguration()
    {
        try {
            $definition = $this->getFormObject()->getDefinition();

            $this->condition->validateConditionConfiguration($definition);
        } catch (InvalidConditionException $exception) {
            $this->throwInvalidConditionException($exception);
        }
    }

    /**
     * @param InvalidConditionException $exception
     * @throws InvalidConditionException
     */
    protected function throwInvalidConditionException(InvalidConditionException $exception)
    {
        $rootObject = $this->getRootObject();
        $conditionName = $this->getConditionName();
        $formClassName = $this->getFormObject()->getClassName();

        if ($rootObject instanceof Field) {
            throw InvalidConditionException::invalidFieldConditionConfiguration($conditionName, $rootObject, $formClassName, $exception);
        } elseif ($rootObject instanceof Validator) {
            throw InvalidConditionException::invalidValidationConditionConfiguration($conditionName, $rootObject, $formClassName, $exception);
        } else {
            throw $exception;
        }
    }

    /**
     * @return string
     */
    public function getConditionName()
    {
        return $this->conditionName;
    }

    /**
     * @return ConditionItemInterface
     */
    public function getCondition()
    {
        return $this->condition;
    }

    public function __sleep()
    {
        $properties = get_object_vars($this);

        unset($properties['formObject']);
        unset($properties['activation']);

        return array_keys($properties);
    }

    /**
     * @return FormObject
     */
    protected function getFormObject()
    {
        return $this->formObject;
    }

    /**
     * @return ActivationUsageInterface
     */
    protected function getRootObject()
    {
        return $this->activation->getRootObject();
    }
}
