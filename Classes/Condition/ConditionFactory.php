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

namespace Romm\Formz\Condition;

use InvalidArgumentException;
use Romm\Formz\Condition\Items\ConditionItemInterface;
use Romm\Formz\Condition\Items\FieldHasErrorCondition;
use Romm\Formz\Condition\Items\FieldHasValueCondition;
use Romm\Formz\Condition\Items\FieldIsEmptyCondition;
use Romm\Formz\Condition\Items\FieldIsValidCondition;
use Romm\Formz\Core\Core;
use Romm\Formz\Exceptions\ClassNotFoundException;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Exceptions\InvalidArgumentTypeException;
use Romm\Formz\Exceptions\MissingArgumentException;
use Romm\Formz\Service\Traits\SelfInstantiateTrait;
use TYPO3\CMS\Core\SingletonInterface;

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
    use SelfInstantiateTrait;

    /**
     * @var array
     */
    private $conditions = [];

    /**
     * @var bool
     */
    private $defaultConditionsWereRegistered = false;

    /**
     * Use this function to register a new condition type which can then be used
     * in the TypoScript configuration. This function should be called from
     * `ext_localconf.php`.
     *
     * The name of the condition must be a valid string, which will be then be
     * used as the identifier for the TypoScript conditions. By convention, you
     * should use the following syntax: `extension_name.condition_name`.
     *
     * The condition class must implement the interface
     * `ConditionItemInterface`.
     *
     * @param string $identifier The identifier of the condition, which will then be available for TypoScript conditions.
     * @param string $className  Class which will process the condition.
     * @return $this
     * @throws ClassNotFoundException
     * @throws InvalidArgumentTypeException
     */
    public function registerCondition($identifier, $className)
    {
        if (false === is_string($identifier)) {
            throw InvalidArgumentTypeException::conditionNameNotString($identifier);
        }

        if (false === class_exists($className)) {
            throw ClassNotFoundException::conditionClassNameNotFound($identifier, $className);
        }

        if (false === in_array(ConditionItemInterface::class, class_implements($className))) {
            throw InvalidArgumentTypeException::conditionClassNameNotValid($className);
        }

        $this->conditions[$identifier] = $className;

        return $this;
    }

    /**
     * @param string $identifier
     * @return bool
     */
    public function hasCondition($identifier)
    {
        return true === array_key_exists($identifier, $this->conditions);
    }

    /**
     * Returns the wanted condition. A check should be done before calling this
     * function, with `hasCondition()`.
     *
     * @param $identifier
     * @return mixed
     * @throws EntryNotFoundException
     */
    public function getCondition($identifier)
    {
        if (false === $this->hasCondition($identifier)) {
            throw EntryNotFoundException::conditionNotFound($identifier, $this->conditions);
        }

        return $this->conditions[$identifier];
    }

    /**
     * @return array
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param string $identifier
     * @param array  $arguments
     * @return ConditionItemInterface
     * @throws EntryNotFoundException
     * @throws MissingArgumentException
     */
    public function instantiateCondition($identifier, array $arguments = [])
    {
        if (false === $this->hasCondition($identifier)) {
            throw EntryNotFoundException::instantiateConditionNotFound($identifier, $this->conditions);
        }

        try {
            /** @var ConditionItemInterface $condition */
            $condition = call_user_func_array(
                [Core::class, 'instantiate'],
                array_merge([$this->conditions[$identifier]], $arguments)
            );
        } catch (InvalidArgumentException $exception) {
            throw MissingArgumentException::conditionConstructorArgumentMissing($identifier, $this->conditions[$identifier], $arguments);
        }

        return $condition;
    }

    /**
     * Registers all default conditions from FormZ core.
     */
    public function registerDefaultConditions()
    {
        if (false === $this->defaultConditionsWereRegistered) {
            $this->defaultConditionsWereRegistered = true;

            $this->registerCondition(
                FieldHasValueCondition::CONDITION_IDENTIFIER,
                FieldHasValueCondition::class
            )->registerCondition(
                FieldHasErrorCondition::CONDITION_IDENTIFIER,
                FieldHasErrorCondition::class
            )->registerCondition(
                FieldIsValidCondition::CONDITION_IDENTIFIER,
                FieldIsValidCondition::class
            )->registerCondition(
                FieldIsEmptyCondition::CONDITION_IDENTIFIER,
                FieldIsEmptyCondition::class
            );
        }
    }
}
