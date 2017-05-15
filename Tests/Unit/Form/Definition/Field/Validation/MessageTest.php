<?php

namespace Romm\Formz\Tests\Unit\Form\Definition\Field\Validation;

use Romm\Formz\Form\Definition\Field\Validation\Message;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class MessageTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function initializationDoneProperly()
    {
        $name = 'foo';

        $validator = new Message($name);

        $this->assertSame($name, $validator->getIdentifier());
    }

    /**
     * @test
     */
    public function setKeySetsKey()
    {
        $message = new Message('foo');
        $message->setKey('foo');
        $this->assertEquals('foo', $message->getKey());
    }

    /**
     * @test
     */
    public function setKeyOnFrozenDefinitionIsChecked()
    {
        $message = $this->getMessageWithDefinitionFreezeStateCheck();

        $message->setKey('foo');
    }

    /**
     * @test
     */
    public function setExtensionSetsExtension()
    {
        $message = new Message('foo');
        $message->setExtension('foo');
        $this->assertEquals('foo', $message->getExtension());
    }

    /**
     * @test
     */
    public function setExtensionOnFrozenDefinitionIsChecked()
    {
        $message = $this->getMessageWithDefinitionFreezeStateCheck();

        $message->setExtension('foo');
    }

    /**
     * @test
     */
    public function setValueSetsValue()
    {
        $message = new Message('foo');
        $message->setValue('foo');
        $this->assertEquals('foo', $message->getValue());
    }

    /**
     * @test
     */
    public function setValueOnFrozenDefinitionIsChecked()
    {
        $message = $this->getMessageWithDefinitionFreezeStateCheck();

        $message->setValue('foo');
    }

    /**
     * @return Message|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMessageWithDefinitionFreezeStateCheck()
    {
        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $message */
        $message = $this->getMockBuilder(Message::class)
            ->setConstructorArgs(['foo'])
            ->setMethods(['checkDefinitionFreezeState'])
            ->getMock();

        $message->expects($this->once())
            ->method('checkDefinitionFreezeState');

        return $message;
    }
}
