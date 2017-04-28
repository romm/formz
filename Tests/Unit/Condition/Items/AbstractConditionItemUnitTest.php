<?php
namespace Romm\Formz\Tests\Unit\Condition\Items;

use Romm\Formz\Condition\Exceptions\InvalidConditionException;
use Romm\Formz\Condition\Items\ConditionItemInterface;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

abstract class AbstractConditionItemUnitTest extends AbstractUnitTest
{
    /**
     * @param $className
     * @param $errorCode
     * @return ConditionItemInterface
     */
    protected function getConditionItemWithFailedConfigurationValidation($className, $errorCode)
    {
        $this->setExpectedException(InvalidConditionException::class, '', $errorCode);

        return $this->getConditionItem($className);
    }

    /**
     * @param string     $className
     * @param FormObject $formObject
     * @return ConditionItemInterface
     */
    protected function getConditionItemWithValidConfigurationValidation($className, FormObject $formObject = null)
    {
        return $this->getConditionItem($className, $formObject);
    }

    /**
     * @param string     $className
     * @param FormObject $formObject
     * @return ConditionItemInterface
     */
    private function getConditionItem($className, FormObject $formObject = null)
    {
        /** @var ConditionItemInterface $conditionItem */
        $conditionItem = new $className;

        $formObject = $formObject ?: $this->getDefaultFormObject();
        $conditionItem->attachFormObject($formObject);

        return $conditionItem;
    }
}
