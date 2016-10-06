<?php
namespace Romm\Formz\Tests\Unit\Form;

use Romm\Formz\Form\FormObject;
use Romm\Formz\Form\FormObjectFactory;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class FormObjectFactoryTest extends AbstractUnitTest
{

    /**
     * Checks that a form object is created and returned.
     *
     * @test
     */
    public function formObjectFromClassNameIsCreated()
    {
        $formObjectFactory = new FormObjectFactory;
        $formConfiguration = [
            'fields' => [
                'foo' => [
                ]
            ]
        ];

        $this->setFormConfigurationFromClassName(DefaultForm::class, $formConfiguration);

        $formObject = $formObjectFactory->getInstanceFromClassName(DefaultForm::class, 'foo');

        $this->assertInstanceOf(FormObject::class, $formObject);
        $this->assertFalse($formObject->getConfigurationObject()->getValidationResult()->hasErrors());
        $this->assertTrue($formObject->getConfiguration()->hasField('foo'));

        unset($formObject);
        unset($formObjectFactory);
    }

    /**
     * A form object instance must be stored in cache the first time it is
     * created, then it should be directly fetched from cache.
     *
     * @test
     */
    public function formObjectFromClassNameIsStoredAndFetchedFromCache()
    {
        $formObject = new FormObject(DefaultForm::class, 'foo');

        /** @var FormObjectFactory|\PHPUnit_Framework_MockObject_MockObject $formObjectFactory */
        $formObjectFactory = $this->getMock(FormObjectFactory::class, ['createInstance']);
        $formObjectFactory->expects($this->once())
            ->method('createInstance')
            ->willReturn($formObject);

        $formObjectFactory->getInstanceFromClassName(DefaultForm::class, 'foo');
        $formObjectFromCache = $formObjectFactory->getInstanceFromClassName(DefaultForm::class, 'foo');

        $this->assertSame($formObject, $formObjectFromCache);

        unset($formObject);
        unset($formObjectFactory);
    }
}
