<?php
namespace Romm\Formz\Tests\Unit\Configuration\Field;

use Romm\Formz\Behaviours\ToLowerCaseBehaviour;
use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Exceptions\SilentException;
use Romm\Formz\Form\Definition\Condition\Activation;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\Definition\Field\Settings\FieldSettings;
use Romm\Formz\Tests\Fixture\Validation\Validator\DummyFieldValidator;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class FieldTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function initializationDoneProperly()
    {
        $fieldName = 'my-field';
        $field = new Field($fieldName);

        $this->assertSame($fieldName, $field->getName());
        $this->assertInstanceOf(FieldSettings::class, $field->getSettings());
    }

    /**
     * @test
     */
    public function addValidatorAddsValidator()
    {
        $validatorName = 'my-validator';
        $field = new Field('foo');

        $this->assertFalse($field->hasValidator($validatorName));
        $validator = $field->addValidator($validatorName, DummyFieldValidator::class);
        $this->assertTrue($field->hasValidator($validatorName));
        $this->assertSame($validator, $field->getValidator($validatorName));
        $this->assertSame([$validatorName => $validator], $field->getValidators());
    }

    /**
     * @test
     */
    public function addValidatorOnFrozenDefinitionIsChecked()
    {
        $field = $this->getFieldWithDefinitionFreezeStateCheck();

        $field->addValidator('my-validator', DummyFieldValidator::class);
    }

    /**
     * @test
     */
    public function addExistingValidatorThrowsException()
    {
        $this->setExpectedException(DuplicateEntryException::class);

        $validatorName = 'my-validator';
        $field = new Field('foo');

        $field->addValidator($validatorName, DummyFieldValidator::class);
        $field->addValidator($validatorName, DummyFieldValidator::class);
    }

    /**
     * @test
     */
    public function getNotExistingValidatorThrowsException()
    {
        $this->setExpectedException(EntryNotFoundException::class);

        $field = new Field('foo');
        $field->getValidator('nope');
    }

    /**
     * @test
     */
    public function addBehaviourAddsBehaviour()
    {
        $behaviourName = 'my-behaviour';
        $field = new Field('foo');

        $this->assertFalse($field->hasBehaviour($behaviourName));
        $behaviour = $field->addBehaviour($behaviourName, ToLowerCaseBehaviour::class);
        $this->assertTrue($field->hasBehaviour($behaviourName));
        $this->assertSame($behaviour, $field->getBehaviour($behaviourName));
        $this->assertSame([$behaviourName => $behaviour], $field->getBehaviours());
    }

    /**
     * @test
     */
    public function addBehaviourOnFrozenDefinitionIsChecked()
    {
        $field = $this->getFieldWithDefinitionFreezeStateCheck();

        $field->addBehaviour('my-behaviour', ToLowerCaseBehaviour::class);
    }

    /**
     * @test
     */
    public function addExistingBehaviourThrowsException()
    {
        $this->setExpectedException(DuplicateEntryException::class);

        $behaviourName = 'my-behaviour';
        $field = new Field('foo');

        $field->addBehaviour($behaviourName, ToLowerCaseBehaviour::class);
        $field->addBehaviour($behaviourName, ToLowerCaseBehaviour::class);
    }

    /**
     * @test
     */
    public function getNotExistingBehaviourThrowsException()
    {
        $this->setExpectedException(EntryNotFoundException::class);

        $field = new Field('foo');
        $field->getBehaviour('nope');
    }

    /**
     * @test
     */
    public function addActivationAddsActivation()
    {
        $field = new Field('foo');

        $this->assertFalse($field->hasActivation());
        $field->addActivation();
        $this->assertTrue($field->hasActivation());
        $this->assertInstanceOf(Activation::class, $field->getActivation());
    }

    /**
     * @test
     */
    public function addActivationOnFrozenDefinitionIsChecked()
    {
        $field = $this->getFieldWithDefinitionFreezeStateCheck();

        $field->addActivation();
    }

    /**
     * @test
     */
    public function getUninitializedActivationThrowsException()
    {
        $this->setExpectedException(SilentException::class);

        $field = new Field('foo');
        $field->getActivation();
    }

    /**
     * @return Field|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFieldWithDefinitionFreezeStateCheck()
    {
        /** @var Field|\PHPUnit_Framework_MockObject_MockObject $field */
        $field = $this->getMockBuilder(Field::class)
            ->setConstructorArgs(['foo'])
            ->setMethods(['checkDefinitionFreezeState'])
            ->getMock();

        $field->expects($this->once())
            ->method('checkDefinitionFreezeState');

        return $field;
    }
}
