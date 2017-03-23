<?php
namespace Romm\Formz\Tests\Unit\Validation\Validator;

use Romm\Formz\Validation\Validator\RequiredValidator;

class RequiredValidatorTest extends AbstractValidatorUnitTest
{
    /**
     * @var string
     */
    protected $validatorClassName = RequiredValidator::class;

    /**
     * Will test if the validator works correctly.
     *
     * @test
     * @dataProvider validatorWorksDataProvider
     * @param        $value
     * @param array  $options
     * @param array  $errors
     */
    public function validatorWorks($value, array $options = [], array $errors = [])
    {
        $this->validateValidator($value, $options, $errors);
    }

    /**
     * @return array
     */
    public function validatorWorksDataProvider()
    {
        $filledArrayObject = new \ArrayObject;
        $filledArrayObject->append('foo');

        return [
            'String'       => [
                'value'   => 'foo'
            ],
            'Empty string' => [
                'value'   => '',
                'options' => [],
                'errors'  => [RequiredValidator::MESSAGE_DEFAULT]
            ],
            'Array' => [
                'value' => ['foo']
            ],
            'Empty array' => [
                'value'   => [],
                'options' => [],
                'errors'  => [RequiredValidator::MESSAGE_DEFAULT]
            ],
            'Filled array object' => [
                'value'   => $filledArrayObject
            ],
            'Empty array object' => [
                'value'   => new \ArrayObject,
                'options' => [],
                'errors'  => [RequiredValidator::MESSAGE_DEFAULT]
            ],
            'Null' => [
                'value'   => null,
                'options' => [],
                'errors'  => [RequiredValidator::MESSAGE_DEFAULT]
            ]
        ];
    }
}
