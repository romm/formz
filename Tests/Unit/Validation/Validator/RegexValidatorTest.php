<?php
namespace Romm\Formz\Tests\Unit\Validation\Validator;

use Romm\Formz\Validation\Validator\RegexValidator;

class RegexValidatorTest extends AbstractValidatorUnitTest
{
    /**
     * @var string
     */
    protected $validatorClassName = RegexValidator::class;

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
            'Preg backtrack limit error'                  => [
                // @see http://php.net/manual/fr/function.preg-last-error.php
                'value'   => 'foobar foobar foobar',
                'options' => [
                    RegexValidator::OPTION_PATTERN => '(?:\D+|<\d+>)*[!?]'
                ],
                'errors'  => [RegexValidator::MESSAGE_DEFAULT]
            ],
            'Value wont match'                            => [
                'value'   => 'foo',
                'options' => [
                    RegexValidator::OPTION_PATTERN => 'bar'
                ],
                'errors'  => [RegexValidator::MESSAGE_DEFAULT]
            ],
            'Upper case is not found'                     => [
                'value'   => 'FOO',
                'options' => [
                    RegexValidator::OPTION_PATTERN => 'foo'
                ],
                'errors'  => [RegexValidator::MESSAGE_DEFAULT]
            ],
            'Upper case with insensitive option is found' => [
                'value'   => 'FOO',
                'options' => [
                    RegexValidator::OPTION_PATTERN => 'foo',
                    RegexValidator::OPTION_OPTIONS => 'i'
                ]
            ],
            'Strong regex'                                => [
                'value'   => 'foo bar baz',
                'options' => [
                    RegexValidator::OPTION_PATTERN => '^foo.*baz$'
                ]
            ]
        ];
    }
}
