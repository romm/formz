<?php
namespace Romm\Formz\Tests\Unit\Configuration\Field\Validation;

use Romm\Formz\Configuration\Form\Field\Validation\Message;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class MessageTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function setKeySetsKey()
    {
        $message = new Message;
        $message->setKey('foo');
        $this->assertEquals('foo', $message->getKey());
    }

    /**
     * @test
     */
    public function setExtensionSetsExtension()
    {
        $message = new Message;
        $message->setExtension('foo');
        $this->assertEquals('foo', $message->getExtension());
    }

    /**
     * @test
     */
    public function setValueSetsValue()
    {
        $message = new Message;
        $message->setValue('foo');
        $this->assertEquals('foo', $message->getValue());
    }
}
