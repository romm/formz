<?php
namespace Romm\Formz\Tests\Unit\Condition\Items;

use Romm\Formz\Condition\Exceptions\InvalidConditionException;
use Romm\Formz\Condition\Items\ConditionItemInterface;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

abstract class AbstractConditionItemUnitTest extends AbstractUnitTest
{
    /**
     * @param       $className
     * @param array $arguments
     * @param       $errorCode
     * @return ConditionItemInterface
     */
    protected function getConditionItemWithFailedConfigurationValidation($className, array $arguments = [], $errorCode)
    {
        $this->setExpectedException(InvalidConditionException::class, '', $errorCode);

        return $this->getConditionItem($className, $arguments);
    }

    /**
     * @param string     $className
     * @param array      $arguments
     * @param FormObject $formObject
     * @return ConditionItemInterface
     */
    protected function getConditionItemWithValidConfigurationValidation($className, array $arguments = [], FormObject $formObject = null)
    {
        return $this->getConditionItem($className, $arguments, $formObject);
    }

    /**
     * @param string     $className
     * @param array      $arguments
     * @param FormObject $formObject
     * @return ConditionItemInterface
     */
    private function getConditionItem($className, array $arguments = [], FormObject $formObject = null)
    {
        $reflection = new \ReflectionClass($className);

        /** @var ConditionItemInterface $conditionItem */
        $conditionItem = $reflection->newInstanceArgs($arguments);

        $formObject = $formObject ?: $this->getDefaultFormObject();
        $conditionItem->attachFormObject($formObject);

        return $conditionItem;
    }
}
