<?php
namespace Romm\Formz\Tests\Unit\Validation\Validator;

use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObject;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Validation\Validator\EqualsToFieldValidator;

class EqualsToFieldValidatorTest extends AbstractValidatorUnitTest
{
    /**
     * @var string
     */
    protected $validatorClassName = EqualsToFieldValidator::class;

    /**
     * Will test if the validator works correctly.
     *
     * @test
     * @dataProvider validatorWorksDataProvider
     * @param string $value
     * @param array  $options
     * @param array  $errors
     * @param string $expectedException
     */
    public function validatorWorks($value, array $options = [], array $errors = [], $expectedException = null)
    {
        if (null !== $expectedException) {
            $this->setExpectedException($expectedException);
        }

        $this->validateValidator($value, $options, $errors);
    }

    /**
     * @return array
     */
    public function validatorWorksDataProvider()
    {
        return [
            'Correct value'                  => [
                'value'   => 'foo',
                'options' => [EqualsToFieldValidator::OPTION_FIELD => 'foo']
            ],
            'Incorrect value'                => [
                'value'   => 'bar',
                'options' => [EqualsToFieldValidator::OPTION_FIELD => 'foo'],
                'errors'  => [EqualsToFieldValidator::MESSAGE_DEFAULT]
            ],
            'Unknown field throws exception' => [
                'value'     => 'bar',
                'options'   => [EqualsToFieldValidator::OPTION_FIELD => 'bar'],
                'errors'    => [],
                'exception' => EntryNotFoundException::class
            ]
        ];
    }

    /**
     * @return FormObject|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFormObject()
    {
        /** @var FormObject|\PHPUnit_Framework_MockObject_MockObject $formObjectMock */
        $formObjectMock = $this->getMockBuilder(FormObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasProperty'])
            ->getMock();

        $formObjectMock->expects($this->at(0))
            ->method('hasProperty')
            ->willReturn(false);

        $formObjectMock->expects($this->at(1))
            ->method('hasProperty')
            ->willReturnCallback(function ($name) {
                return $name === 'foo';
            });

        $formObjectMock->expects($this->exactly(2))
            ->method('hasProperty');

        $formObjectMock->addProperty('foo');

        $formObjectMock->setForm(new DefaultForm);

        return $formObjectMock;
    }

    /**
     * @return FormInterface
     */
    protected function getForm()
    {
        $formProphecy = $this->prophesize(DefaultForm::class);
        $formProphecy->getFoo()
            ->willReturn('bar');

        /** @var FormInterface $form */
        $form = $formProphecy->reveal();

        return $form;
    }
}
