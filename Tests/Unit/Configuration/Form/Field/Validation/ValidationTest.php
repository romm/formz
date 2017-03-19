<?php
namespace Romm\Formz\Tests\Unit\Configuration\Field\Validation;

use Romm\Formz\Configuration\Form\Field\Activation\ActivationInterface;
use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Configuration\Form\Field\Validation\Message;
use Romm\Formz\Configuration\Form\Field\Validation\Validation;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Validation\Validator\RequiredValidator;

class ValidationTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function initializationDoneProperly()
    {
        $validation = new Validation;

        $this->assertInstanceOf(ActivationInterface::class, $validation->getActivation());
    }

    /**
     * @test
     */
    public function setClassNameSetsClassName()
    {
        $validation = new Validation;

        $validation->setClassName(RequiredValidator::class);
        $this->assertEquals(RequiredValidator::class, $validation->getClassName());
    }

    /**
     * @test
     */
    public function setPrioritySetsPriority()
    {
        $validation = new Validation;

        $validation->setPriority(404);
        $this->assertEquals(404, $validation->getPriority());
    }

    /**
     * @test
     */
    public function setOptionsSetsOptions()
    {
        $validation = new Validation;
        $options = ['foo' => 'bar'];

        $validation->setOptions($options);
        $this->assertEquals($options, $validation->getOptions());
    }

    /**
     * @test
     */
    public function setMessagesSetsMessages()
    {
        $validation = new Validation;
        $messages = ['foo' => new Message];

        $validation->setMessages($messages);
        $this->assertEquals($messages, $validation->getMessages());
    }

    /**
     * @test
     */
    public function setActivationSetsActivation()
    {
        $validation = new Validation;

        /** @var ActivationInterface|\PHPUnit_Framework_MockObject_MockObject $activation */
        $activation = $this->getMockBuilder(ActivationInterface::class)
            ->setMethods(['setRootObject'])
            ->getMockForAbstractClass();

        $activation->expects($this->once())
            ->method('setRootObject')
            ->with($validation);

        $this->assertFalse($validation->hasActivation());
        $validation->setActivation($activation);
        $this->assertTrue($validation->hasActivation());
        $this->assertSame($activation, $validation->getActivation());
    }

    /**
     * @test
     */
    public function setNameSetsName()
    {
        $validation = new Validation;

        $validation->setName('foo');
        $this->assertEquals('foo', $validation->getName());
    }

    /**
     * @test
     */
    public function activateAjaxUsageActivatesAjaxUsage()
    {
        $validation = new Validation;

        $this->assertFalse($validation->doesUseAjax());
        $validation->activateAjaxUsage();
        $this->assertTrue($validation->doesUseAjax());
    }

    /**
     * @test
     */
    public function parentFieldCanBeFetched()
    {
        $validation = new Validation;
        $field = new Field;

        $validation->setParents([$field]);
        $this->assertSame($field, $validation->getParentField());
    }
}
