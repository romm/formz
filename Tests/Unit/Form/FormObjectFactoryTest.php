<?php
namespace Romm\Formz\Tests\Unit\Form;

use Romm\Formz\Configuration\ConfigurationFactory;
use Romm\Formz\Core\Core;
use Romm\Formz\Exceptions\ClassNotFoundException;
use Romm\Formz\Exceptions\InvalidArgumentTypeException;
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
        /** @var ConfigurationFactory $configurationFactory */
        $configurationFactory = Core::instantiate(ConfigurationFactory::class);

        $formObjectFactory = new FormObjectFactory;
        $formObjectFactory->injectConfigurationFactory($configurationFactory);
        $formObjectFactory->injectTypoScriptService($this->getMockedTypoScriptService());

        $formObject = $formObjectFactory->getInstanceFromClassName(DefaultForm::class, 'foo');

        $this->assertInstanceOf(FormObject::class, $formObject);
        $this->assertFalse($formObject->getConfigurationValidationResult()->hasErrors());
        $this->assertTrue($formObject->getConfiguration()->hasField('foo'));

        unset($formObject);
        unset($formObjectFactory);
    }

    /**
     * Check that an exception is thrown when sending a class name that does not
     * exist.
     *
     * @test
     */
    public function wrongClassNameGivenThrowsException()
    {
        $this->setExpectedException(ClassNotFoundException::class);
        $formObjectFactory = new FormObjectFactory;

        $formObjectFactory->getInstanceFromClassName('foo', 'foo');
    }

    /**
     * Checks that there is a check on the inheritance of the given class name.
     *
     * @test
     */
    public function wrongClassTypeGivenThrowsException()
    {
        $this->setExpectedException(InvalidArgumentTypeException::class);
        $formObjectFactory = new FormObjectFactory;

        $formObjectFactory->getInstanceFromClassName(\stdClass::class, 'foo');
    }

    /**
     * A form object instance must be stored in cache the first time it is
     * created, then it should be directly fetched from cache.
     *
     * @test
     */
    public function formObjectFromClassNameIsStoredAndFetchedFromCache()
    {
        $formObject = new FormObject(DefaultForm::class, 'foo', []);

        /** @var ConfigurationFactory $configurationFactory */
        $configurationFactory = Core::instantiate(ConfigurationFactory::class);

        /** @var FormObjectFactory|\PHPUnit_Framework_MockObject_MockObject $formObjectFactory */
        $formObjectFactory = $this->getMockBuilder(FormObjectFactory::class)
            ->setMethods(['createInstance'])
            ->getMock();
        $formObjectFactory->injectConfigurationFactory($configurationFactory);

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
