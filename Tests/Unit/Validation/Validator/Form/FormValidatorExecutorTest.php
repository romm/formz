<?php

namespace Romm\Formz\Tests\Unit\Validation\Validator\Form;

use Romm\Formz\Error\FormResult;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Validation\Form\DataObject\FormValidatorDataObject;
use Romm\Formz\Validation\Form\FormValidatorExecutor;
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

        $fieldFoo = $formObject->getDefinition()->getField('foo');
        $fieldBar = $formObject->getDefinition()->getField('bar');

        /** @var FormValidatorExecutor|\PHPUnit_Framework_MockObject_MockObject $formValidatorExecutor */
        $formValidatorExecutor = $this->getMockBuilder(FormValidatorExecutor::class)
            ->setMethods(['checkFieldActivation'])
            ->setConstructorArgs([$this->getFormValidatorDataObjectDummy($formObject)])
            ->getMock();

        $formValidatorExecutor->expects($this->exactly(2))
            ->method('checkFieldActivation')
            ->withConsecutive(
                [$fieldFoo],
                [$fieldBar]
            )
            ->willReturnCallback(function (Field $field) use ($formValidatorExecutor) {
                $formValidatorExecutor->getResult()->deactivateField($field);
            });

        $formValidatorExecutor->checkFieldsActivation();
        $formValidatorExecutor->checkFieldsActivation();
        $formValidatorExecutor->checkFieldsActivation();
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

        $field = $formObject->getDefinition()->getField('foo');
        $field->addActivation();

        /** @var FormValidatorExecutor|\PHPUnit_Framework_MockObject_MockObject $formValidatorExecutor */
        $formValidatorExecutor = $this->getMockBuilder(FormValidatorExecutor::class)
            ->setMethods(['getFieldActivationProcessResult'])
            ->setConstructorArgs([$this->getFormValidatorDataObjectDummy($formObject)])
            ->getMock();

        $formValidatorExecutor->expects($this->once())
            ->method('getFieldActivationProcessResult')
            ->with($field)
            ->willReturn(false);

        $result = $formValidatorExecutor->getResult();

        $this->assertFalse($result->fieldIsDeactivated($field));
        $formValidatorExecutor->checkFieldsActivation();
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

        $field = $formObject->getDefinition()->getField('foo');
        $validator = $field->addValidator('foo', RequiredValidator::class);
        $validator->addActivation();

        /** @var FormValidatorExecutor|\PHPUnit_Framework_MockObject_MockObject $formValidatorExecutor */
        $formValidatorExecutor = $this->getMockBuilder(FormValidatorExecutor::class)
            ->setMethods(['getValidatorActivationProcessResult'])
            ->setConstructorArgs([$this->getFormValidatorDataObjectDummy($formObject)])
            ->getMock();

        $formValidatorExecutor->expects($this->once())
            ->method('getValidatorActivationProcessResult')
            ->with($validator)
            ->willReturn(false);

        $result = $formValidatorExecutor->getResult();

        $this->assertFalse($result->validatorIsDeactivated($validator));
        $formValidatorExecutor->checkFieldsActivation();
        $this->assertTrue($result->validatorIsDeactivated($validator));
    }

    /**
     * Runs a simple "required" check on an empty field, and checks that the
     * error is added to the result.
     *
     * @test
     */
    public function errorIsAddedToFieldValidation()
    {
        $form = new DefaultForm;
        $form->setFoo('');
        $formObject = $this->getDefaultFormObject();

        $form = new DefaultForm;
        $form->setFoo('');
        $formObject->setForm($form);

        $field = $formObject->getDefinition()->getField('foo');

        $field->addValidator('foo', RequiredValidator::class);

        $formValidatorExecutor = new FormValidatorExecutor($this->getFormValidatorDataObjectDummy($formObject));

        $result = $formValidatorExecutor->getResult();

        $this->assertFalse($result->forProperty('foo')->hasErrors());
        $formValidatorExecutor->validateFields();
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

        $validator = $field->addValidator('foo', RequiredValidator::class);
        $validator->addActivation();

        /** @var FormValidatorExecutor|\PHPUnit_Framework_MockObject_MockObject $formValidatorExecutor */
        $formValidatorExecutor = $this->getMockBuilder(FormValidatorExecutor::class)
            ->setMethods(['getValidatorActivationProcessResult'])
            ->setConstructorArgs([$this->getFormValidatorDataObjectDummy($formObject)])
            ->getMock();

        $formValidatorExecutor->expects($this->once())
            ->method('getValidatorActivationProcessResult')
            ->with($validator)
            ->willReturn(false);

        $this->assertFalse($result->forProperty('foo')->hasErrors());
        $formValidatorExecutor->validateFields();
        $this->assertFalse($result->forProperty('foo')->hasErrors());
    }

    /**
     * @param FormObject $formObject
     * @return FormValidatorDataObject
     */
    protected function getFormValidatorDataObjectDummy(FormObject $formObject)
    {
        return new FormValidatorDataObject($formObject, new FormResult);
    }
}
