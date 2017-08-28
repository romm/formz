<?php
namespace Romm\Formz\Tests\Fixture\Validation\Validator;

use Romm\Formz\Validation\Validator\AbstractValidator;

class DummyFieldValidator extends AbstractValidator
{
    const MESSAGE_1 = 'message1';

    /**
     * @var array
     */
    public static $javaScriptValidationFiles = ['foo', 'bar'];

    /**
     * @var array
     */
    protected $supportedMessages = [
        self::MESSAGE_1 => [
            'value' => 'message: %s'
        ]
    ];

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @param mixed $value
     */
    public function isValid($value)
    {
        if (is_callable($this->callback)) {
            call_user_func($this->callback, $this, $value);
        }
    }

    /**
     * @param callable $callback
     */
    public function setCallBack(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param string  $key
     * @param string  $code
     * @param array   $arguments
     * @param  string $title
     */
    public function addNewError($key, $code, array $arguments, $title)
    {
        $this->addError($key, $code, $arguments, $title);
    }

    /**
     * @param string  $key
     * @param string  $code
     * @param array   $arguments
     * @param  string $title
     */
    public function addNewWarning($key, $code, array $arguments, $title)
    {
        $this->addWarning($key, $code, $arguments, $title);
    }

    /**
     * @param string  $key
     * @param string  $code
     * @param array   $arguments
     * @param  string $title
     */
    public function addNewNotice($key, $code, array $arguments, $title)
    {
        $this->addNotice($key, $code, $arguments, $title);
    }
}
