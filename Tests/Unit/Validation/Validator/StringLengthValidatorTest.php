<?php
namespace Romm\Formz\Tests\Unit\Validation\Validator;

use Romm\Formz\Validation\Validator\StringLengthValidator;

class StringLengthValidatorTest extends AbstractValidatorUnitTest
{
    /**
     * @var string
     */
    protected $validatorClassName = StringLengthValidator::class;

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
        return [
            'Empty string'   => [
                'value' => ''
            ],
            'Default string' => [
                'value' => 'foo'
            ],
            'Short string'   => [
                'value'   => 'foo',
                'options' => [
                    StringLengthValidator::OPTION_MINIMUM => 42
                ],
                'errors'  => [StringLengthValidator::MESSAGE_DEFAULT]
            ],
            'Long string'    => [
                'value'   => 'foo bar baz',
                'options' => [
                    StringLengthValidator::OPTION_MAXIMUM => 3
                ],
                'errors'  => [StringLengthValidator::MESSAGE_DEFAULT]
            ]
        ];
    }
}
