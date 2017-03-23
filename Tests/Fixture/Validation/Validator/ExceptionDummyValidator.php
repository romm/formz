<?php
namespace Romm\Formz\Tests\Fixture\Validation\Validator;

use Romm\Formz\Validation\Validator\AbstractValidator;

class ExceptionDummyValidator extends AbstractValidator
{
    /**
     * @param mixed $value
     * @throws \Exception
     */
    public function isValid($value)
    {
        throw new \Exception('hello world!', 1337);
    }
}
