<?php
namespace Romm\Formz\Tests\Unit\Condition\Items;

use Romm\Formz\Condition\Items\FieldIsValidCondition;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use Romm\Formz\Error\Error;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Validation\Validator\Form\FormValidatorExecutor;

class FieldIsValidConditionTest extends AbstractConditionItemUnitTest
{
    /**
     * @test
     */
    public function wrongFieldNameThrowsException()
    {
        /** @var FieldIsValidCondition $conditionItem */
        $conditionItem = $this->getConditionItemWithFailedConfigurationValidation(FieldIsValidCondition::class, 1488183577);
        $conditionItem->setFieldName('baz');
        $conditionItem->validateConditionConfiguration();
    }

    /**
     * @test
     */
    public function validConfiguration()
    {
        /** @var FieldIsValidCondition $conditionItem */
        $conditionItem = $this->getConditionItemWithValidConfigurationValidation(FieldIsValidCondition::class);
        $conditionItem->setFieldName('foo');
        $conditionItem->validateConditionConfiguration();
    }

    /**
     * The field has errors.
     *
     * @test
     */
    public function phpConditionIsNotVerifiedWhenFieldHasErrors()
    {
        $conditionItem = new FieldIsValidCondition;
        $conditionItem->setFieldName('foo');

        $formObject = $this->getDefaultFormObject();
        $conditionItem->attachFormObject($formObject);

        $field = $formObject->getConfiguration()->getField('foo');

        /** @var FormValidatorExecutor|\PHPUnit_Framework_MockObject_MockObject $formValidator */
        $formValidator = $this->getMockBuilder(FormValidatorExecutor::class)
            ->disableOriginalConstructor()
            ->setMethods(['validateField', 'getResult'])
            ->getMock();

        $formValidator->expects($this->once())
            ->method('validateField')
            ->with($field);

        $formResult = new FormResult;
        $error = new Error('foo', 42, 'bar', 'baz');
        $formResult->forProperty('foo')->addError($error);

        $formValidator->expects($this->once())
            ->method('getResult')
            ->willReturn($formResult);

        $phpConditionDataObject = new PhpConditionDataObject(new DefaultForm, $formValidator);

        $result = $conditionItem->getPhpResult($phpConditionDataObject);

        $this->assertFalse($result);
    }

    /**
     * The field has no error.
     *
     * @test
     */
    public function phpConditionIsVerifiedWhenFieldHasNoErrors()
    {
        $conditionItem = new FieldIsValidCondition;
        $conditionItem->setFieldName('foo');

        $formObject = $this->getDefaultFormObject();
        $conditionItem->attachFormObject($formObject);

        $field = $formObject->getConfiguration()->getField('foo');

        /** @var FormValidatorExecutor|\PHPUnit_Framework_MockObject_MockObject $formValidator */
        $formValidator = $this->getMockBuilder(FormValidatorExecutor::class)
            ->disableOriginalConstructor()
            ->setMethods(['validateField', 'getResult'])
            ->getMock();

        $formValidator->expects($this->once())
            ->method('validateField')
            ->with($field);

        $formValidator->expects($this->once())
            ->method('getResult')
            ->willReturn(new FormResult);

        $phpConditionDataObject = new PhpConditionDataObject(new DefaultForm, $formValidator);

        $result = $conditionItem->getPhpResult($phpConditionDataObject);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function getCssResultEmpty()
    {
        $conditionItem = new FieldIsValidCondition;
        $conditionItem->setFieldName('foo');

        $this->assertEquals('[formz-valid-foo="1"]', $conditionItem->getCssResult());
    }

    /**
     * @test
     */
    public function getJavaScriptResult()
    {
        $assert = 'Formz.Condition.validateCondition(\'Romm\\\\Formz\\\\Condition\\\\Items\\\\FieldIsValidCondition\', form, {"fieldName":"foo"})';

        $conditionItem = new FieldIsValidCondition;
        $conditionItem->setFieldName('foo');

        $this->assertEquals($assert, $conditionItem->getJavaScriptResult());
    }
}
