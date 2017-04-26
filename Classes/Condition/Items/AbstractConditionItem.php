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

use Romm\ConfigurationObject\Traits\ConfigurationObject\MagicMethodsTrait;
use Romm\Formz\Condition\Exceptions\InvalidConditionException;
use Romm\Formz\Condition\Parser\Node\ConditionNode;
use Romm\Formz\Form\Definition\Field\Activation\ActivationInterface;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\Definition\Field\Validation\Validation;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Service\ArrayService;

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
     * @var FormObject
     * @disableMagicMethods
     */
    protected $formObject;

    /**
     * @var ActivationInterface
     * @disableMagicMethods
     */
    protected $activation;

    /**
     * @var ConditionNode
     * @disableMagicMethods
     */
    protected $conditionNode;

    /**
     * Will launch the condition validation: the child class should implement
     * the function `checkConditionConfiguration()` and check if the condition
     * can be considered as valid.
     *
     * If any syntax/configuration error is found, an exception of type
     * `InvalidConditionException` must be thrown.
     *
     * @throws InvalidConditionException
     * @return bool
     */
    final public function validateConditionConfiguration()
    {
        try {
            $this->checkConditionConfiguration();
        } catch (InvalidConditionException $exception) {
            $this->throwInvalidConditionException($exception);
        }

        return true;
    }

    /**
     * @see validateConditionConfiguration()
     *
     * @throws InvalidConditionException
     * @return bool
     */
    abstract protected function checkConditionConfiguration();

    /**
     * Returns a generic JavaScript code which uses FormZ API to validate a
     * condition which was registered in a single JavaScript file (which is
     * filled in the `$javaScriptFiles` attribute of the PHP condition class).
     *
     * @param array $data
     * @return string
     */
    protected function getDefaultJavaScriptCall(array $data)
    {
        $conditionName = addslashes(get_class($this));
        $data = ArrayService::get()->arrayToJavaScriptJson($data);

        return <<<JS
Fz.Condition.validateCondition('$conditionName', form, $data)
JS;
    }

    /**
     * @return array
     */
    public function getJavaScriptFiles()
    {
        return static::$javaScriptFiles;
    }

    /**
     * @param InvalidConditionException $exception
     * @throws InvalidConditionException
     */
    protected function throwInvalidConditionException(InvalidConditionException $exception)
    {
        $rootObject = $this->activation->getRootObject();
        $conditionName = $this->conditionNode->getConditionName();
        $formClassName = $this->formObject->getClassName();

        if ($rootObject instanceof Field) {
            throw InvalidConditionException::invalidFieldConditionConfiguration($conditionName, $rootObject, $formClassName, $exception);
        } elseif ($rootObject instanceof Validation) {
            throw InvalidConditionException::invalidValidationConditionConfiguration($conditionName, $rootObject, $formClassName, $exception);
        }
    }

    /**
     * @param FormObject $formObject
     */
    public function attachFormObject(FormObject $formObject)
    {
        $this->formObject = $formObject;
    }

    /**
     * @param ActivationInterface $activation
     */
    public function attachActivation(ActivationInterface $activation)
    {
        $this->activation = $activation;
    }

    /**
     * @param ConditionNode $conditionNode
     */
    public function attachConditionNode(ConditionNode $conditionNode)
    {
        $this->conditionNode = $conditionNode;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        $properties = get_object_vars($this);
        unset($properties['formObject']);
        unset($properties['activation']);
        unset($properties['conditionNode']);

        return array_keys($properties);
    }
}
