<?php
namespace Romm\Formz\Tests\Unit\Validation\Validator;

use Romm\Formz\Validation\Validator\IsIntegerValidator;

class IsIntegerValidatorTest extends AbstractValidatorUnitTest
{
    /**
     * @var string
     */
    protected $validatorClassName = IsIntegerValidator::class;

    /**
     * Will test if the validator works correctly.
     *
     * @test
     * @dataProvider validatorWorksDataProvider
     * @param       $value
     * @param array $options
     * @param array $errors
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
        return [
            'Negative integer' => [
                'value' => -42
            ],
            'Positive integer' => [
                'value' => 42
            ],
            'Float'            => [
                'value'   => 42.1337,
                'options' => [],
                'errors'  => [IsIntegerValidator::MESSAGE_DEFAULT]
            ],
            'String'           => [
                'value'   => 'foo',
                'options' => [],
                'errors'  => [IsIntegerValidator::MESSAGE_DEFAULT]
            ],
            'Boolean'          => [
                'value'   => false,
                'options' => [],
                'errors'  => [IsIntegerValidator::MESSAGE_DEFAULT]
            ],
            'Array'            => [
                'value'   => [],
                'options' => [],
                'errors'  => [IsIntegerValidator::MESSAGE_DEFAULT]
            ],
            'Object'           => [
                'value'   => new \stdClass,
                'options' => [],
                'errors'  => [IsIntegerValidator::MESSAGE_DEFAULT]
            ],
            'Callable'         => [
                'value'   => function () {
                },
                'options' => [],
                'errors'  => [IsIntegerValidator::MESSAGE_DEFAULT]
            ]
        ];
    }
}
