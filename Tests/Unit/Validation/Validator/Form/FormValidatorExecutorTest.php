<?php
namespace Romm\Formz\Tests\Unit\Validation\Validator\Form;

use Romm\Formz\Form\Definition\Field\Activation\Activation;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\Definition\Field\Validation\Validation;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Fixture\Form\ExtendedForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Validation\Validator\Form\FormValidatorExecutor;
use Romm\Formz\Validation\Validator\RequiredValidator;

class FormValidatorExecutorTest extends AbstractUnitTest
{
    /**
     * When checking all fields activations, the check must run only once per
     * field.
     *
     * @test
     */
    public function fieldActivationIsCalledOncePerField()
    {
        $formObject = $this->getExtendedFormObject();
        $formObject->setForm(new DefaultForm);
        $result = $formObject->getFormResult();

        $fieldFoo = $formObject->getDefinition()->getField('foo');
        $fieldBar = $formObject->getDefinition()->getField('bar');

        /** @var FormValidatorExecutor|\PHPUnit_Framework_MockObject_MockObject $formzValidatorExecutor */
        $formzValidatorExecutor = $this->getMockBuilder(FormValidatorExecutor::class)
            ->setMethods(['getFormObject', 'checkFieldActivation'])
            ->setConstructorArgs([new ExtendedForm, 'foo'])
            ->getMock();

        $formzValidatorExecutor->expects($this->exactly(2))
            ->method('checkFieldActivation')
            ->withConsecutive(
                [$fieldFoo],
                [$fieldBar]
            )
            ->willReturnCallback(function (Field $field) use ($result) {
                $result->deactivateField($field);
            });

        $formzValidatorExecutor->method('getFormObject')
            ->willReturn($formObject);

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
        $formObject->setForm(new DefaultForm);
        $result = $formObject->getFormResult();

        $field = $formObject->getDefinition()->getField('foo');
        $field->setActivation(new Activation);

        /** @var FormValidatorExecutor|\PHPUnit_Framework_MockObject_MockObject $formzValidatorExecutor */
        $formzValidatorExecutor = $this->getMockBuilder(FormValidatorExecutor::class)
            ->setMethods(['getFormObject', 'getFieldActivationProcessResult'])
            ->setConstructorArgs([new DefaultForm, 'foo'])
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
        $formObject->setForm(new DefaultForm);
        $result = $formObject->getFormResult();

        $field = $formObject->getDefinition()->getField('foo');
        $validation = new Validation;
        $field->addValidation($validation);

        $validation->setClassName(RequiredValidator::class);
        $validation->setParents([$field]);

        $activation = new Activation;
        $validation->setActivation($activation);

        /** @var FormValidatorExecutor|\PHPUnit_Framework_MockObject_MockObject $formzValidatorExecutor */
        $formzValidatorExecutor = $this->getMockBuilder(FormValidatorExecutor::class)
            ->setMethods(['getFormObject', 'getValidationActivationProcessResult'])
            ->setConstructorArgs([new DefaultForm, 'foo'])
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
        $formObject->setForm(new DefaultForm);
        $result = $formObject->getFormResult();

        $field = $formObject->getDefinition()->getField('foo');

        $validation = new Validation;
        $field->addValidation($validation);

        $validation->setClassName(RequiredValidator::class);
        $validation->setParents([$field]);

        $form = new DefaultForm;
        $form->setFoo('');

        /** @var FormValidatorExecutor|\PHPUnit_Framework_MockObject_MockObject $formzValidatorExecutor */
        $formzValidatorExecutor = $this->getMockBuilder(FormValidatorExecutor::class)
            ->setMethods(['getFormObject'])
            ->setConstructorArgs([$form, 'foo'])
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
        $formObject->setForm(new DefaultForm);
        $result = $formObject->getFormResult();

        $field = $formObject->getDefinition()->getField('foo');

        $validation = new Validation;
        $field->addValidation($validation);

        $validation->setClassName(RequiredValidator::class);
        $validation->setParents([$field]);

        $activation = new Activation;
        $validation->setActivation($activation);

        /** @var FormValidatorExecutor|\PHPUnit_Framework_MockObject_MockObject $formzValidatorExecutor */
        $formzValidatorExecutor = $this->getMockBuilder(FormValidatorExecutor::class)
            ->setMethods(['getFormObject', 'getValidationActivationProcessResult'])
            ->setConstructorArgs([new DefaultForm, 'foo'])
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
