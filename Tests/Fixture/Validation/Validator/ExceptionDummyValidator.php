<?php
namespace Romm\Formz\Tests\Fixture\Validation\Validator;

use Romm\Formz\Validation\Field\AbstractFieldValidator;

class ExceptionDummyValidator extends AbstractFieldValidator
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
