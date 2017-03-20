<?php
namespace Romm\Formz\Tests\Unit\Configuration\Field;

use Romm\Formz\Configuration\Form\Field\Activation\ActivationInterface;
use Romm\Formz\Configuration\Form\Field\Behaviour\Behaviour;
use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Configuration\Form\Field\Settings\FieldSettings;
use Romm\Formz\Configuration\Form\Field\Validation\Validation;
use Romm\Formz\Configuration\Form\Form;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class FieldTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function initializationDoneProperly()
    {
        $field = new Field;

        $this->assertInstanceOf(FieldSettings::class, $field->getSettings());
        $this->assertInstanceOf(ActivationInterface::class, $field->getActivation());
    }

    /**
     * @test
     */
    public function parentFormIsFetched()
    {
        $field = new Field;
        $form = new Form;

        $field->setParents([$form]);
        $this->assertSame($form, $field->getForm());
    }

    /**
     * @test
     */
    public function addValidationAddsValidation()
    {
        $field = new Field;
        $validation = new Validation;
        $validation->setName('foo');

        $this->assertFalse($field->hasValidation('foo'));
        $field->addValidation($validation);
        $this->assertTrue($field->hasValidation('foo'));
        $this->assertSame($validation, $field->getValidationByName('foo'));
        $this->assertSame(['foo' => $validation], $field->getValidation());
    }

    /**
     * @test
     */
    public function validationNotFoundThrowsException()
    {
        $this->setExpectedException(EntryNotFoundException::class);

        $field = new Field;
        $field->getValidationByName('nope');
    }

    /**
     * @test
     */
    public function addBehaviourAddsBehaviour()
    {
        $field = new Field;
        $behaviour = new Behaviour;

        $this->assertEmpty($field->getBehaviours());
        $field->addBehaviour('foo', $behaviour);
        $this->assertEquals(['foo' => $behaviour], $field->getBehaviours());
    }

    /**
     * @test
     */
    public function setActivationSetsActivation()
    {
        $field = new Field;
        /** @var ActivationInterface|\PHPUnit_Framework_MockObject_MockObject $activation */
        $activation = $this->getMockBuilder(ActivationInterface::class)
            ->setMethods(['setRootObject'])
            ->getMockForAbstractClass();

        $activation->expects($this->once())
            ->method('setRootObject')
            ->with($field);

        $this->assertFalse($field->hasActivation());
        $field->setActivation($activation);
        $this->assertTrue($field->hasActivation());
        $this->assertSame($activation, $field->getActivation());
    }

    /**
     * @test
     */
    public function setNameSetsName()
    {
        $field = new Field;

        $field->setName('foo');
        $this->assertEquals('foo', $field->getName());
    }
}
