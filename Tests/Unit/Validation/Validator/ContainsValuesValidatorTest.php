<?php
namespace Romm\Formz\Tests\Unit\Validation\Validator;

use Romm\Formz\Validation\Validator\ContainsValuesValidator;

class ContainsValuesValidatorTest extends AbstractValidatorUnitTest
{
    /**
     * @var string
     */
    protected $validatorClassName = ContainsValuesValidator::class;

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
            'Foo is found'                                   => [
                'value'   => 'foo',
                'options' => [
                    ContainsValuesValidator::OPTION_VALUES => ['foo', 'bar']
                ]
            ],
            'Foo in array is found'                          => [
                'value'   => ['foo'],
                'options' => [
                    ContainsValuesValidator::OPTION_VALUES => ['foo', 'bar']
                ]
            ],
            'Bar is found'                                   => [
                'value'   => 'bar',
                'options' => [
                    ContainsValuesValidator::OPTION_VALUES => ['foo', 'bar']
                ]
            ],
            'Bar in array is found'                          => [
                'value'   => ['bar'],
                'options' => [
                    ContainsValuesValidator::OPTION_VALUES => ['foo', 'bar']
                ]
            ],
            'Foo and bar values are found'                   => [
                'value'   => ['foo', 'bar'],
                'options' => [
                    ContainsValuesValidator::OPTION_VALUES => ['foo', 'bar']
                ]
            ],
            'Baz is not found'                               => [
                'value'   => 'baz',
                'options' => [
                    ContainsValuesValidator::OPTION_VALUES => ['foo', 'bar']
                ],
                'errors'  => [ContainsValuesValidator::MESSAGE_DEFAULT]
            ],
            'Baz in array is not found'                      => [
                'value'   => ['baz'],
                'options' => [
                    ContainsValuesValidator::OPTION_VALUES => ['foo', 'bar']
                ],
                'errors'  => [ContainsValuesValidator::MESSAGE_DEFAULT]
            ],
            'Baz in array with other values is not found'    => [
                'value'   => ['foo', 'bar', 'baz'],
                'options' => [
                    ContainsValuesValidator::OPTION_VALUES => ['foo', 'bar']
                ],
                'errors'  => [ContainsValuesValidator::MESSAGE_DEFAULT]
            ],
            'Foo is found in pipe-separated string'          => [
                'value'   => 'foo',
                'options' => [
                    ContainsValuesValidator::OPTION_VALUES => 'foo|bar'
                ]
            ],
            'Bar is found in pipe-separated string'          => [
                'value'   => 'bar',
                'options' => [
                    ContainsValuesValidator::OPTION_VALUES => 'foo|bar'
                ]
            ],
            'Foo and bar are found in pipe-separated string' => [
                'value'   => ['foo', 'bar'],
                'options' => [
                    ContainsValuesValidator::OPTION_VALUES => 'foo|bar'
                ]
            ],
            'Baz is not found in pipe-separated string'      => [
                'value'   => 'baz',
                'options' => [
                    ContainsValuesValidator::OPTION_VALUES => 'foo|bar'
                ],
                'errors'  => [ContainsValuesValidator::MESSAGE_DEFAULT]
            ],
            'Empty value is not accepted'                    => [
                'value'   => [],
                'options' => [
                    ContainsValuesValidator::OPTION_VALUES => ['foo', 'bar']
                ],
                'errors'  => [ContainsValuesValidator::MESSAGE_EMPTY]
            ]
        ];
    }
}
