<?php

namespace Romm\Formz\Tests\Unit\Condition\Items;

use Romm\Formz\Condition\Items\FieldHasErrorCondition;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use Romm\Formz\Error\Error;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Validation\Form\FormValidatorExecutor;
use Romm\Formz\Validation\Validator\RequiredValidator;

class FieldHasErrorConditionTest extends AbstractConditionItemUnitTest
{
    /**
     * @test
     */
    public function wrongFieldNameThrowsException()
    {
        /** @var FieldHasErrorCondition $conditionItem */
        $conditionItem = $this->getConditionItemWithFailedConfigurationValidation(
            FieldHasErrorCondition::class,
            [
                'fieldName'      => 'baz',
                'validationName' => 'baz'
            ],
            1488192037
        );
        $conditionItem->validateConditionConfiguration($this->getDefaultFormObject()->getDefinition());
    }

    /**
     * @test
     */
    public function wrongValidationNameThrowsException()
    {
        /** @var FieldHasErrorCondition $conditionItem */
        $conditionItem = $this->getConditionItemWithFailedConfigurationValidation(
            FieldHasErrorCondition::class,
            [
                'fieldName'      => 'foo',
                'validationName' => 'baz'
            ],
            1488192055
        );
        $conditionItem->validateConditionConfiguration($this->getDefaultFormObject()->getDefinition());
    }

    /**
     * @test
     */
    public function validConfiguration()
    {
        $formObject = $this->getDefaultFormObject();
        $formObject->getDefinition()->getField('foo')->addValidator('bar', RequiredValidator::class);

        /** @var FieldHasErrorCondition $conditionItem */
        $conditionItem = $this->getConditionItemWithValidConfigurationValidation(
            FieldHasErrorCondition::class,
            [
                'fieldName'      => 'foo',
                'validationName' => 'bar'
            ],
            $formObject
        );
        $conditionItem->validateConditionConfiguration($formObject->getDefinition());
    }

    /**
     * The error is not found is the field.
     *
     * @test
     */
    public function phpConditionIsNotVerifiedWithWrongValidationName()
    {
        $conditionItem = new FieldHasErrorCondition('foo', 'not defined');

        $formObject = $this->getDefaultFormObject();
        $conditionItem->attachFormObject($formObject);

        $field = $formObject->getDefinition()->getField('foo');

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
        $conditionItem = new FieldHasErrorCondition('foo', 'bar', 'not defined');

        $formObject = $this->getDefaultFormObject();
        $conditionItem->attachFormObject($formObject);

        $field = $formObject->getDefinition()->getField('foo');

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
        $conditionItem = new FieldHasErrorCondition('foo', 'bar', 'baz');

        $formObject = $this->getDefaultFormObject();
        $conditionItem->attachFormObject($formObject);

        $field = $formObject->getDefinition()->getField('foo');

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
        $conditionItem = new FieldHasErrorCondition('foo', 'bar', 'baz');

        $this->assertEquals('[fz-error-foo-bar-baz="1"]', $conditionItem->getCssResult());
    }

    /**
     * @test
     */
    public function getJavaScriptResult()
    {
        $assert = 'Fz.Condition.validateCondition(\'Romm\\\\Formz\\\\Condition\\\\Items\\\\FieldHasErrorCondition\', form, {"fieldName":"foo","validationName":"bar","errorName":"baz"})';

        $conditionItem = new FieldHasErrorCondition('foo', 'bar', 'baz');

        $this->assertEquals($assert, $conditionItem->getJavaScriptResult());
    }
}
