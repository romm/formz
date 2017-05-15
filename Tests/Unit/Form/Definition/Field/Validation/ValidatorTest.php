<?php
namespace Romm\Formz\Tests\Unit\Form\Definition\Field\Validation;

use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Exceptions\SilentException;
use Romm\Formz\Form\Definition\Condition\Activation;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\Definition\Field\Validation\Validator;
use Romm\Formz\Tests\Fixture\Validation\Validator\DummyValidator;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class ValidatorTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function initializationDoneProperly()
    {
        $name = 'foo';
        $className = DummyValidator::class;

        $validator = new Validator($name, $className);

        $this->assertSame($name, $validator->getName());
        $this->assertSame($className, $validator->getClassName());
    }

    /**
     * @test
     */
    public function setPrioritySetsPriority()
    {
        $validator = new Validator('foo', DummyValidator::class);

        $validator->setPriority(404);
        $this->assertEquals(404, $validator->getPriority());
    }

    /**
     * @test
     */
    public function setPriorityOnFrozenDefinitionIsChecked()
    {
        $validator = $this->getValidatorWithDefinitionFreezeStateCheck();

        $validator->setPriority(404);
    }

    /**
     * @test
     */
    public function setOptionsSetsOptions()
    {
        $validator = new Validator('foo', DummyValidator::class);
        $options = ['foo' => 'bar'];

        $validator->setOptions($options);
        $this->assertEquals($options, $validator->getOptions());
    }

    /**
     * @test
     */
    public function setOptionOnFrozenDefinitionIsChecked()
    {
        $validator = $this->getValidatorWithDefinitionFreezeStateCheck();

        $validator->setOptions(['foo' => 'bar']);
    }

    /**
     * @test
     */
    public function addMessageAddsMessage()
    {
        $messageIdentifier = 'my-message';
        $validator = new Validator('foo', DummyValidator::class);

        $this->assertFalse($validator->hasMessage($messageIdentifier));
        $message = $validator->addMessage($messageIdentifier);
        $this->assertTrue($validator->hasMessage($messageIdentifier));
        $this->assertSame($message, $validator->getMessage($messageIdentifier));
        $this->assertSame([$messageIdentifier => $message], $validator->getMessages());
    }

    /**
     * @test
     */
    public function addMessageOnFrozenDefinitionIsChecked()
    {
        $validator = $this->getValidatorWithDefinitionFreezeStateCheck();

        $validator->addMessage('foo');
    }

    /**
     * @test
     */
    public function addExistingMessageThrowsException()
    {
        $this->setExpectedException(DuplicateEntryException::class);

        $validator = new Validator('foo', DummyValidator::class);
        $validator->addMessage('foo');
        $validator->addMessage('foo');
    }

    /**
     * @test
     */
    public function getNotExistingMessageThrowsException()
    {
        $this->setExpectedException(EntryNotFoundException::class);

        $validator = new Validator('foo', DummyValidator::class);
        $validator->getMessage('foo');
    }

    /**
     * @test
     */
    public function addActivationAddsActivation()
    {
        $validator = new Validator('foo', DummyValidator::class);

        $this->assertFalse($validator->hasActivation());
        $validator->addActivation();
        $this->assertTrue($validator->hasActivation());
        $this->assertInstanceOf(Activation::class, $validator->getActivation());
    }

    /**
     * @test
     */
    public function addActivationOnFrozenDefinitionIsChecked()
    {
        $validator = $this->getValidatorWithDefinitionFreezeStateCheck();

        $validator->addActivation();
    }

    /**
     * @test
     */
    public function getUninitializedActivationThrowsException()
    {
        $this->setExpectedException(SilentException::class);

        $validator = new Validator('foo', DummyValidator::class);
        $validator->getActivation();
    }

    /**
     * @test
     */
    public function activateAjaxUsageActivatesAjaxUsage()
    {
        $validator = new Validator('foo', DummyValidator::class);

        $this->assertFalse($validator->doesUseAjax());
        $validator->activateAjaxUsage();
        $this->assertTrue($validator->doesUseAjax());
        $validator->deactivateAjaxUsage();
        $this->assertFalse($validator->doesUseAjax());
    }

    /**
     * @test
     */
    public function activateAjaxUsageOnFrozenDefinitionIsChecked()
    {
        $validator = $this->getValidatorWithDefinitionFreezeStateCheck();

        $validator->activateAjaxUsage();
    }

    /**
     * @test
     */
    public function deactivateAjaxUsageOnFrozenDefinitionIsChecked()
    {
        $validator = $this->getValidatorWithDefinitionFreezeStateCheck();

        $validator->deactivateAjaxUsage();
    }

    /**
     * @test
     */
    public function parentFieldCanBeFetched()
    {
        $validator = new Validator('foo', DummyValidator::class);
        $field = new Field('foo');

        $validator->attachParent($field);
        $this->assertSame($field, $validator->getParentField());
    }

    /**
     * @return Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getValidatorWithDefinitionFreezeStateCheck()
    {
        /** @var Validator|\PHPUnit_Framework_MockObject_MockObject $validator */
        $validator = $this->getMockBuilder(Validator::class)
            ->setConstructorArgs(['foo', DummyValidator::class])
            ->setMethods(['checkDefinitionFreezeState'])
            ->getMock();

        $validator->expects($this->once())
            ->method('checkDefinitionFreezeState');

        return $validator;
    }
}
