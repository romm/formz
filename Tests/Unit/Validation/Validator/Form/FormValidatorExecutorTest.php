<?php
namespace Romm\Formz\Tests\Unit\Validation\Validator\Form;

use Romm\Formz\Configuration\Form\Condition\Activation\Activation;
use Romm\Formz\Configuration\Form\Field\Validation\Validation;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Fixture\Form\ExtendedForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Validation\Validator\Form\FormValidatorExecutor;
use Romm\Formz\Validation\Validator\RequiredValidator;

class FormValidatorExecutorTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function getResultReturnsConstructorResult()
    {
        $result = new FormResult;
        $formValidatorExecutor = new FormValidatorExecutor(new DefaultForm, 'foo', $result);
        $this->assertSame($result, $formValidatorExecutor->getResult());
    }

    /**
     * When checking all fields activations, the check must run only once per
     * field.
     *
     * @test
     */
    public function fieldActivationIsCalledOncePerField()
    {
        $formObject = $this->getExtendedFormObject();
        $fieldFoo = $formObject->getConfiguration()->getField('foo');
        $fieldBar = $formObject->getConfiguration()->getField('bar');

        $result = new FormResult;

        /** @var FormValidatorExecutor|\PHPUnit_Framework_MockObject_MockObject $formzValidatorExecutor */
        $formzValidatorExecutor = $this->getMockBuilder(FormValidatorExecutor::class)
            ->setMethods(['getFormObject', 'checkFieldActivation'])
            ->setConstructorArgs([new ExtendedForm, 'foo', $result])
            ->getMock();

        $formzValidatorExecutor->method('getFormObject')
            ->willReturn($formObject);

        $formzValidatorExecutor->expects($this->at(1))
            ->method('checkFieldActivation')
            ->with($fieldFoo)
            ->willReturnCallback(function () use ($fieldFoo, $result) {
                $result->deactivateField($fieldFoo);
            });

        $formzValidatorExecutor->expects($this->at(2))
            ->method('checkFieldActivation')
            ->with($fieldBar)
            ->willReturnCallback(function () use ($fieldBar, $result) {
                $result->deactivateField($fieldBar);
            });

        $formzValidatorExecutor->expects($this->exactly(2))
            ->method('checkFieldActivation');

        $formzValidatorExecutor->checkFieldsActivation();
        $formzValidatorExecutor->checkFieldsActivation();
        $formzValidatorExecutor->checkFieldsActivation();
    }

    /**
     * Checking that a field is correctly deactivated when it should be.
     *
     * @test
     */
    public function fieldActivationCheckRunsCorrectly()
    {
        $formObject = $this->getDefaultFormObject();
        $field = $formObject->getConfiguration()->getField('foo');
        $field->setActivation(new Activation);

        $result = new FormResult;

        /** @var FormValidatorExecutor|\PHPUnit_Framework_MockObject_MockObject $formzValidatorExecutor */
        $formzValidatorExecutor = $this->getMockBuilder(FormValidatorExecutor::class)
            ->setMethods(['getFormObject', 'getFieldActivationProcessResult'])
            ->setConstructorArgs([new DefaultForm, 'foo', $result])
            ->getMock();

        $formzValidatorExecutor->method('getFormObject')
            ->willReturn($formObject);

        $formzValidatorExecutor->expects($this->once())
            ->method('getFieldActivationProcessResult')
            ->with($field)
            ->willReturn(false);

        $this->assertFalse($result->fieldIsDeactivated($field));
        $formzValidatorExecutor->checkFieldsActivation();
        $this->assertTrue($result->fieldIsDeactivated($field));
    }

    /**
     * Checking that a validation is correctly deactivated when it should be.
     *
     * @test
     */
    public function fieldValidationActivationCheckRunsCorrectly()
    {
        $formObject = $this->getDefaultFormObject();
        $field = $formObject->getConfiguration()->getField('foo');
        $validation = new Validation;
        $field->addValidation($validation);

        $validation->setClassName(RequiredValidator::class);
        $validation->setParents([$field]);

        $activation = new Activation;
        $validation->setActivation($activation);

        $result = new FormResult;

        /** @var FormValidatorExecutor|\PHPUnit_Framework_MockObject_MockObject $formzValidatorExecutor */
        $formzValidatorExecutor = $this->getMockBuilder(FormValidatorExecutor::class)
            ->setMethods(['getFormObject', 'getValidationActivationProcessResult'])
            ->setConstructorArgs([new DefaultForm, 'foo', $result])
            ->getMock();

        $formzValidatorExecutor->method('getFormObject')
            ->willReturn($formObject);

        $formzValidatorExecutor->expects($this->once())
            ->method('getValidationActivationProcessResult')
            ->with($validation)
            ->willReturn(false);

        $this->assertFalse($result->validationIsDeactivated($validation));
        $formzValidatorExecutor->checkFieldsActivation();
        $this->assertTrue($result->validationIsDeactivated($validation));
    }

    /**
     * Runs a simple "required" check on an empty field, and checks that the
     * error is added to the result.
     *
     * @test
     */
    public function errorIsAddedToFieldValidation()
    {
        $formObject = $this->getDefaultFormObject();
        $field = $formObject->getConfiguration()->getField('foo');

        $validation = new Validation;
        $field->addValidation($validation);

        $validation->setClassName(RequiredValidator::class);
        $validation->setParents([$field]);

        $result = new FormResult;

        /** @var FormValidatorExecutor|\PHPUnit_Framework_MockObject_MockObject $formzValidatorExecutor */
        $formzValidatorExecutor = $this->getMockBuilder(FormValidatorExecutor::class)
            ->setMethods(['getFormObject'])
            ->setConstructorArgs([new DefaultForm, 'foo', $result])
            ->getMock();

        $formzValidatorExecutor->method('getFormObject')
            ->willReturn($formObject);

        $this->assertFalse($result->forProperty('foo')->hasErrors());
        $formzValidatorExecutor->validateFields();
        $this->assertTrue($result->forProperty('foo')->hasErrors());
    }

    /**
     * @test
     */
    public function validationIsCorrectlyDeactivatedWhenItHasCondition()
    {
        $formObject = $this->getDefaultFormObject();
        $field = $formObject->getConfiguration()->getField('foo');

        $validation = new Validation;
        $field->addValidation($validation);

        $validation->setClassName(RequiredValidator::class);
        $validation->setParents([$field]);

        $activation = new Activation;
        $validation->setActivation($activation);

        $result = new FormResult;

        /** @var FormValidatorExecutor|\PHPUnit_Framework_MockObject_MockObject $formzValidatorExecutor */
        $formzValidatorExecutor = $this->getMockBuilder(FormValidatorExecutor::class)
            ->setMethods(['getFormObject', 'getValidationActivationProcessResult'])
            ->setConstructorArgs([new DefaultForm, 'foo', $result])
            ->getMock();

        $formzValidatorExecutor->method('getFormObject')
            ->willReturn($formObject);

        $formzValidatorExecutor->expects($this->once())
            ->method('getValidationActivationProcessResult')
            ->with($validation)
            ->willReturn(false);

        $this->assertFalse($result->forProperty('foo')->hasErrors());
        $formzValidatorExecutor->validateFields();
        $this->assertFalse($result->forProperty('foo')->hasErrors());
    }
}
