<?php
namespace Romm\Formz\Tests\Fixture\Form;

use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormTrait;

class DefaultForm implements FormInterface
{
    use FormTrait;

    /**
     * @var string
     */
    protected $foo;

    /**
     * @return string
     */
    public function getFoo()
    {
        return $this->foo;
    }

    /**
     * @param string $foo
     */
    public function setFoo($foo)
    {
        $this->foo = $foo;
    }
}
