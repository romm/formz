<?php
namespace Romm\Formz\Tests\Unit\Validation\Validator;

use Romm\Formz\Validation\Validator\BetweenNumbersValidator;

class BetweenNumbersValidatorTest extends AbstractValidatorUnitTest
{
    /**
     * @var string
     */
    protected $validatorClassName = BetweenNumbersValidator::class;

    /**
     * Will test if the validator works correctly.
     *
     * @test
     * @dataProvider validatorWorksDataProvider
     * @param       $value
     * @param array $options
     * @param array $errors
     */
    public function validatorWorks($value, array $options, array $errors = [])
    {
        $this->validateValidator($value, $options, $errors);
    }

    /**
     * @return array
     */
    public function validatorWorksDataProvider()
    {
        return [
            'Minimum is correct'      => [
                'value'   => 0,
                'options' => [
                    BetweenNumbersValidator::OPTION_MINIMUM => 0,
                    BetweenNumbersValidator::OPTION_MAXIMUM => 10
                ]
            ],
            'In between is correct'   => [
                'value'   => 5,
                'options' => [
                    BetweenNumbersValidator::OPTION_MINIMUM => 0,
                    BetweenNumbersValidator::OPTION_MAXIMUM => 10
                ]
            ],
            'Maximum is correct'      => [
                'value'   => 10,
                'options' => [
                    BetweenNumbersValidator::OPTION_MINIMUM => 0,
                    BetweenNumbersValidator::OPTION_MAXIMUM => 10
                ]
            ],
            'Below is incorrect'      => [
                'value'   => -5,
                'options' => [
                    BetweenNumbersValidator::OPTION_MINIMUM => 0,
                    BetweenNumbersValidator::OPTION_MAXIMUM => 10
                ],
                [BetweenNumbersValidator::MESSAGE_DEFAULT]
            ],
            'Above is incorrect'      => [
                'value'   => 15,
                'options' => [
                    BetweenNumbersValidator::OPTION_MINIMUM => 0,
                    BetweenNumbersValidator::OPTION_MAXIMUM => 10
                ],
                [BetweenNumbersValidator::MESSAGE_DEFAULT]
            ],
            'Not number is incorrect' => [
                'value'   => 'foo',
                'options' => [
                    BetweenNumbersValidator::OPTION_MINIMUM => 0,
                    BetweenNumbersValidator::OPTION_MAXIMUM => 10
                ],
                [BetweenNumbersValidator::MESSAGE_NOT_NUMBER]
            ]
        ];
    }
}
