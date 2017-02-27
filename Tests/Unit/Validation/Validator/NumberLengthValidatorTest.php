<?php
namespace Romm\Formz\Tests\Unit\Validation\Validator;

use Romm\Formz\Validation\Validator\NumberLengthValidator;

class NumberLengthValidatorTest extends AbstractValidatorUnitTest
{
    /**
     * @var string
     */
    protected $validatorClassName = NumberLengthValidator::class;

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
            'Not number' => [
                'value' => 'foo',
                'options' => [],
                'errors' => [NumberLengthValidator::MESSAGE_NOT_NUMBER]
            ],
            'Between #1' => [
                'value' => 42,
                'options' => [NumberLengthValidator::OPTION_MAXIMUM => 10]
            ],
            'Between #2' => [
                'value' => 42,
                'options' => [NumberLengthValidator::OPTION_MAXIMUM => 2]
            ],
            'Between #3' => [
                'value' => 42,
                'options' => [
                    NumberLengthValidator::OPTION_MINIMUM => -1,
                    NumberLengthValidator::OPTION_MAXIMUM => 2
                ]
            ],
            'Between #4' => [
                'value' => 42,
                'options' => [
                    NumberLengthValidator::OPTION_MINIMUM => 1,
                    NumberLengthValidator::OPTION_MAXIMUM => 2
                ]
            ],
            'Between #5' => [
                'value' => 42,
                'options' => [
                    NumberLengthValidator::OPTION_MINIMUM => 2,
                    NumberLengthValidator::OPTION_MAXIMUM => 2
                ]
            ],
            'Less' => [
                'value' => 42,
                'options' => [
                    NumberLengthValidator::OPTION_MINIMUM => 5,
                    NumberLengthValidator::OPTION_MAXIMUM => 5
                ],
                'errors' => [NumberLengthValidator::MESSAGE_DEFAULT]
            ],
            'More' => [
                'value' => 1337,
                'options' => [NumberLengthValidator::OPTION_MAXIMUM => 2],
                'errors' => [NumberLengthValidator::MESSAGE_DEFAULT]
            ]
        ];
    }
}
