<?php
namespace Romm\Formz\Tests\Unit\Condition\Items;

use Romm\Formz\Condition\Items\FieldHasErrorCondition;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use Romm\Formz\Configuration\Form\Field\Validation\Validation;
use Romm\Formz\Error\Error;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Validation\Validator\Form\FormValidatorExecutor;

class FieldHasErrorConditionTest extends AbstractConditionItemUnitTest
{
    /**
     * @test
     */
    public function wrongFieldNameThrowsException()
    {
        /** @var FieldHasErrorCondition $conditionItem */
        $conditionItem = $this->getConditionItemWithFailedConfigurationValidation(FieldHasErrorCondition::class, 1488192037);
        $conditionItem->setFieldName('baz');
        $conditionItem->validateConditionConfiguration();
    }

    /**
     * @test
     */
    public function wrongValidationNameThrowsException()
    {
        /** @var FieldHasErrorCondition $conditionItem */
        $conditionItem = $this->getConditionItemWithFailedConfigurationValidation(FieldHasErrorCondition::class, 1488192055);
        $conditionItem->setFieldName('foo');
        $conditionItem->setValidationName('baz');
        $conditionItem->validateConditionConfiguration();
    }

    /**
     * @test
     */
    public function validConfiguration()
    {
        $validation = new Validation;
        $validation->setValidationName('bar');
        $formObject = $this->getDefaultFormObject();
        $formObject->getConfiguration()->getField('foo')->addValidation($validation);

        /** @var FieldHasErrorCondition $conditionItem */
        $conditionItem = $this->getConditionItemWithValidConfigurationValidation(FieldHasErrorCondition::class, $formObject);
        $conditionItem->setFieldName('foo');
        $conditionItem->setValidationName('bar');
        $conditionItem->validateConditionConfiguration();
    }

    /**
     * The error is not found is the field.
     *
     * @test
     */
    public function phpConditionIsNotVerifiedWithWrongValidationName()
    {
        $conditionItem = new FieldHasErrorCondition;
        $conditionItem->setFieldName('foo');
        $conditionItem->setValidationName('not defined');

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

        $this->assertFalse($result);
    }

    /**
     * The error is not found is the field.
     *
     * @test
     */
    public function phpConditionIsNotVerifiedWithWrongErrorName()
    {
        $conditionItem = new FieldHasErrorCondition;
        $conditionItem->setFieldName('foo');
        $conditionItem->setValidationName('bar');
        $conditionItem->setErrorName('not defined');

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
     * The error is found is the field.
     *
     * @test
     */
    public function phpConditionIsVerified()
    {
        $conditionItem = new FieldHasErrorCondition;
        $conditionItem->setFieldName('foo');
        $conditionItem->setValidationName('bar');
        $conditionItem->setErrorName('baz');

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

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function getCssResult()
    {
        $conditionItem = new FieldHasErrorCondition;
        $conditionItem->setFieldName('foo');
        $conditionItem->setValidationName('bar');
        $conditionItem->setErrorName('baz');

        $this->assertEquals('[formz-error-foo-bar-baz="1"]', $conditionItem->getCssResult());
    }

    /**
     * @test
     */
    public function getJavaScriptResult()
    {
        $assert = 'Fz.Condition.validateCondition(\'Romm\\\\Formz\\\\Condition\\\\Items\\\\FieldHasErrorCondition\', form, {"fieldName":"foo","validationName":"bar","errorName":"baz"})';

        $conditionItem = new FieldHasErrorCondition;
        $conditionItem->setFieldName('foo');
        $conditionItem->setValidationName('bar');
        $conditionItem->setErrorName('baz');

        $this->assertEquals($assert, $conditionItem->getJavaScriptResult());
    }
}
