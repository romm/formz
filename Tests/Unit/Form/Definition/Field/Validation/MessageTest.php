<?php
namespace Romm\Formz\Tests\Unit\Form\Definition\Field\Validation;

use Romm\Formz\Form\Definition\Field\Validation\Message;
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
