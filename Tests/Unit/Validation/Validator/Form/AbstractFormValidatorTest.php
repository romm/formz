<?php
namespace Romm\Formz\Tests\Unit\Validation\Validator\Form;

use Romm\Formz\Exceptions\InvalidArgumentTypeException;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Fixture\Validation\Validator\Form\DummyFormValidator;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Validation\Validator\Form\DefaultFormValidator;
use Romm\Formz\Validation\Validator\Form\FormValidatorExecutor;

class AbstractFormValidatorTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function validatingUnknownFormClassThrowsException()
    {
        $this->setExpectedException(InvalidArgumentTypeException::class);

        $validator = new DefaultFormValidator(['name' => 'foo']);

        /** @noinspection PhpParamsInspection */
        $validator->validate(new \stdClass);
    }

    /**
     * Methods must be called in a specific order.
     *
     * @test
     */
    public function validatorExecutorMethodsAreCalledInRightOrder()
    {
        /** @var DummyFormValidator|\PHPUnit_Framework_MockObject_MockObject $validatorMock */
        $validatorMock = $this->getMockBuilder(DummyFormValidator::class)
            ->setMethods(['getFormValidatorExecutor'])
            ->setConstructorArgs([['name' => 'foo']])
            ->getMock();

        /** @var FormValidatorExecutor|\PHPUnit_Framework_MockObject_MockObject $formValidatorExecutorMock */
        $formValidatorExecutorMock = $this->getMockBuilder(FormValidatorExecutor::class)
            ->setMethods(['applyBehaviours', 'checkFieldsActivation', 'validateFields'])
            ->disableOriginalConstructor()
            ->getMock();

        $validatorMock->expects($this->once())
            ->method('getFormValidatorExecutor')
            ->willReturn($formValidatorExecutorMock);

        $counter = 0;

        $formValidatorExecutorMock->expects($this->once())
            ->method('applyBehaviours')
            ->willReturnCallback(function () use (&$counter) {
                $this->assertEquals(0, $counter);
                $counter++;
            });

        $formValidatorExecutorMock->expects($this->once())
            ->method('checkFieldsActivation')
            ->willReturnCallback(function () use (&$counter) {
                $this->assertEquals(1, $counter);
                $counter++;
            });

        $formValidatorExecutorMock->expects($this->once())
            ->method('validateFields')
            ->willReturnCallback(function () use (&$counter) {
                $this->assertEquals(2, $counter);
            });

        $validatorMock->validate(new DefaultForm);
    }
}
