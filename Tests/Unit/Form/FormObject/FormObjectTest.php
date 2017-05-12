<?php
namespace Romm\Formz\Tests\Unit\Form\FormObject;

use Prophecy\Prophecy\ObjectProphecy;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Exceptions\PropertyNotAccessibleException;
use Romm\Formz\Form\Definition\FormDefinition;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Form\FormObject\FormObjectProxy;
use Romm\Formz\Form\FormObject\FormObjectStatic;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Extbase\Error\Result;

class FormObjectTest extends AbstractUnitTest
{
    /**
     * The name passed as constructor argument should be gettable.
     *
     * @test
     */
    public function formNameCanBeRetrieved()
    {
        $formName = 'foo';
        $formObject = new FormObject($formName, $this->getEmptyFormObjectStaticMock());

        $this->assertEquals($formName, $formObject->getName());
    }

    /**
     * All data bound to the static instance can be retrieved with getter
     * functions.
     *
     * @test
     */
    public function staticDataCanBeRetrieved()
    {
        $className = DefaultForm::class;
        $definition = $this->prophesize(FormDefinition::class)->reveal();
        $definitionValidationResult = new Result;
        $objectHash = 'foo-bar-baz';

        /** @var FormObjectStatic|ObjectProphecy $static */
        $static = $this->prophesize(FormObjectStatic::class);

        $static->getClassName()
            ->shouldBeCalled()
            ->willReturn($className);

        $static->getDefinition()
            ->shouldBeCalled()
            ->willReturn($definition);

        $static->getDefinitionValidationResult()
            ->shouldBeCalled()
            ->willReturn($definitionValidationResult);

        $static->getObjectHash()
            ->shouldBeCalled()
            ->willReturn($objectHash);

        $formObject = new FormObject('foo', $static->reveal());

        $this->assertSame($className, $formObject->getClassName());
        $this->assertSame($definition, $formObject->getDefinition());
        $this->assertSame($definitionValidationResult, $formObject->getDefinitionValidationResult());
        $this->assertSame($objectHash, $formObject->getObjectHash());
    }

    /**
     * Setting a form instance for the form object should change some
     * behaviours.
     *
     * @test
     */
    public function setFormSetsForm()
    {
        /** @var FormObject|\PHPUnit_Framework_MockObject_MockObject $formObject */
        $formObject = $this->getMockBuilder(FormObject::class)
            ->setConstructorArgs(['foo', $this->prophesize(FormObjectStatic::class)->reveal()])
            ->setMethods(['createProxy'])
            ->getMock();

        $form = new DefaultForm;

        $formObject->expects($this->once())
            ->method('createProxy')
            ->with($form)
            ->willReturnCallback(function (FormInterface $form) use ($formObject) {
                return new FormObjectProxy($formObject, $form);
            });

        $this->assertFalse($formObject->hasForm());
        $formObject->setForm($form);
        $this->assertTrue($formObject->hasForm());
        $this->assertSame($form, $formObject->getForm());
    }

    /**
     * When trying to add a form instance in a form object that already has one,
     * an exception must be thrown.
     *
     * @test
     */
    public function addingFormInstanceMoreThanOnceThrowsException()
    {
        $this->setExpectedException(DuplicateEntryException::class);

        $formObject = new FormObject('foo', $this->getEmptyFormObjectStaticMock());
        $form = new DefaultForm;

        $formObject->setForm($form);
        $formObject->setForm($form);
    }

    /**
     * When calling functions directly bound to the proxy, and this one has not
     * been initialized yet, an exception must be thrown.
     *
     * @test
     */
    public function proxyNotInitializedThrowsException()
    {
        $this->setExpectedException(PropertyNotAccessibleException::class);

        $formObject = new FormObject('foo', $this->getEmptyFormObjectStaticMock());

        $formObject->getForm();
    }

    /**
     * All data bound to the proxy instance can be retrieved with proper
     * functions.
     *
     * @test
     */
    public function proxyDataCanBeRetrieved()
    {
        $formResult = new FormResult;
        $formHash = 'foo-bar-baz';

        /** @var FormObjectProxy|ObjectProphecy $proxy */
        $proxy = $this->prophesize(FormObjectProxy::class);

        $proxy->formWasSubmitted()
            ->shouldBeCalled();

        $proxy->formWasValidated()
            ->shouldBeCalled();

        $proxy->getFormResult()
            ->shouldBeCalled()
            ->willReturn($formResult);

        $proxy->getFormHash()
            ->shouldBeCalled()
            ->willReturn($formHash);

        /** @var FormObject|\PHPUnit_Framework_MockObject_MockObject $formObject */
        $formObject = $this->getMockBuilder(FormObject::class)
            ->setConstructorArgs(['foo', $this->prophesize(FormObjectStatic::class)->reveal()])
            ->setMethods(['createProxy'])
            ->getMock();

        $formObject->method('createProxy')
            ->willReturn($proxy->reveal());

        $formObject->setForm(new DefaultForm);

        $formObject->formWasSubmitted();
        $formObject->formWasValidated();
        $this->assertSame($formResult, $formObject->getFormResult());
        $this->assertSame($formHash, $formObject->getFormHash());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FormObjectStatic
     */
    protected function getEmptyFormObjectStaticMock()
    {
        return $this->getMockBuilder(FormObjectStatic::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
