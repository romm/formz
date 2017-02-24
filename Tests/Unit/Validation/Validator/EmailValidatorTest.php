<?php
namespace Romm\Formz\Tests\Unit\Validation\Validator;

use Romm\Formz\Validation\Validator\EmailValidator;

class EmailValidatorTest extends AbstractValidatorUnitTest
{
    /**
     * @var string
     */
    protected $validatorClassName = EmailValidator::class;

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
        /*
         * We wont test too much different email adresses because the email
         * validator relies and TYPO3 core function for email checking.
         *
         * We're just going to see if both invalid and valid email examples
         * behave normally.
         */
        return [
            'Invalid email is not accepted' => [
                'value'   => 'invalid email',
                'options' => [],
                'errors'  => [EmailValidator::MESSAGE_DEFAULT]
            ],
            'Valid email is accepted'       => [
                'value'   => 'john.doe@i-love-typo3.com',
                'options' => []
            ]
        ];
    }
}
