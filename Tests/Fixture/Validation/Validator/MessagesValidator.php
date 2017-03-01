<?php
namespace Romm\Formz\Tests\Fixture\Validation\Validator;

use Romm\Formz\Validation\Validator\AbstractValidator;

class MessagesValidator extends AbstractValidator
{
    const MESSAGE_1 = 'message1';
    const MESSAGE_2 = 'message2';
    const MESSAGE_3 = 'message3';

    /**
     * @var array
     */
    protected $supportedMessages = [
        self::MESSAGE_1 => [
            'value' => self::MESSAGE_1
        ],
        self::MESSAGE_2 => [
            'value' => self::MESSAGE_2
        ],
        self::MESSAGE_3 => [
            'value' => self::MESSAGE_3
        ]
    ];

    /**
     * @param mixed $value
     */
    public function isValid($value)
    {
        $this->addError(self::MESSAGE_1, 42);
        $this->addWarning(self::MESSAGE_2, 42);
        $this->addNotice(self::MESSAGE_3, 42);
    }
}
