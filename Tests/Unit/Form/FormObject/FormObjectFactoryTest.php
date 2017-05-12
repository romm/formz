<?php

namespace Romm\Formz\Tests\Unit\Form\FormObject;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Exceptions\ClassNotFoundException;
use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Exceptions\InvalidArgumentTypeException;
use Romm\Formz\Exceptions\InvalidArgumentValueException;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Form\FormObject\FormObjectFactory;
use Romm\Formz\Form\FormObject\FormObjectStatic;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\UnitTestContainer;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Extbase\Error\Result;

class FormObjectFactoryTest extends AbstractUnitTest
{
    /**
     * @var FrontendInterface
     */
    protected $cacheInstance;

    /**
     * Trying to get the form object instance for a given form instance that was
     * not registered must throw an exception.
     *
     * @test
     */
    public function getUnregisteredInstanceThrowsException()
    {
        $this->setExpectedException(EntryNotFoundException::class);

        $formObjectFactory = $this->getFormObjectFactory();
        $form = new DefaultForm;

        $this->assertFalse($formObjectFactory->formInstanceWasRegistered($form));
        $formObjectFactory->getInstanceWithFormInstance($form);
    }

    /**
     * The name of a registered form must be filled, or an exception is thrown.
     *
     * @test
     */
    public function registerFormInstanceWithEmptyNameThrowsException()
    {
        $this->setExpectedException(InvalidArgumentValueException::class);

        $formObjectFactory = $this->getFormObjectFactory();
        $formObjectFactory->registerFormInstance(new DefaultForm, '');
    }

    /**
     * A form instance can be registered only once.
     *
     * @test
     */
    public function registerFormInstanceThrowsException()
    {
        $this->setExpectedException(DuplicateEntryException::class);

        $formObjectFactory = $this->getFormObjectFactory();
        $form = new DefaultForm;

        $formObjectFactory->registerFormInstance($form, 'foo');
        $formObjectFactory->registerFormInstance($form, 'foo');
    }

    /**
     * Checks that when a form instance was registered, its form object can be
     * retrieved.
     *
     * @test
     */
    public function registerFormInstanceWorksAsExpected()
    {
        $formObjectFactory = $this->getFormObjectFactory();
        $form = new DefaultForm;

        $this->assertFalse($formObjectFactory->formInstanceWasRegistered($form));
        $formObjectFactory->registerFormInstance($form, 'foo');
        $this->assertTrue($formObjectFactory->formInstanceWasRegistered($form));
        $this->assertInstanceOf(FormObject::class, $formObjectFactory->getInstanceWithFormInstance($form));
    }

    /**
     * @test
     */
    public function registerAndGetFormInstanceWorksAsExpected()
    {
        $formObjectFactory = $this->getFormObjectFactory();
        $form = new DefaultForm;

        $formObject = $formObjectFactory->registerAndGetFormInstance($form, 'foo');
        $this->assertInstanceOf(FormObject::class, $formObject);

        $formObject2 = $formObjectFactory->registerAndGetFormInstance($form, 'foo');
        $this->assertSame($formObject, $formObject2);
    }

    /**
     * Checks that a form object is created and returned.
     *
     * @test
     */
    public function formObjectFromClassNameIsCreated()
    {
        $formObjectFactory = $this->getFormObjectFactory();
        $formObject = $formObjectFactory->getInstanceWithClassName(DefaultForm::class, 'foo');

        $this->assertInstanceOf(FormObject::class, $formObject);
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

        $formObjectFactory->getInstanceWithClassName('foo', 'foo');
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

        $formObjectFactory->getInstanceWithClassName(\stdClass::class, 'foo');
    }

    /**
     * The static form object instance of a given form class must be stored in
     * local cache.
     *
     * @test
     */
    public function formObjectStaticUsesMemoization()
    {
        $formObjectFactory = $this->getFormObjectFactory();
        $formObjectFactory->expects($this->once())
            ->method('buildStaticInstance');

        $formObjectFactory->getInstanceWithClassName(DefaultForm::class, 'foo');
        $formObjectFactory->getInstanceWithClassName(DefaultForm::class, 'foo');
        $formObjectFactory->getInstanceWithClassName(DefaultForm::class, 'foo');
    }

    /**
     * A form object instance must be stored in cache the first time it is
     * created, then it should be directly fetched from cache.
     *
     * @test
     */
    public function formObjectStaticIsStoredInCache()
    {
        $formObjectFactory1 = $this->getFormObjectFactory();
        $formObjectFactory1->expects($this->once())
            ->method('buildStaticInstance');

        $formObjectFactory2 = $this->getFormObjectFactory();
        $formObjectFactory2->expects($this->never())
            ->method('buildStaticInstance');

        $formObjectFactory1->getInstanceWithClassName(DefaultForm::class, 'foo');
        $formObjectFactory1->getInstanceWithClassName(DefaultForm::class, 'foo');
        $formObjectFactory2->getInstanceWithClassName(DefaultForm::class, 'foo');
        $formObjectFactory2->getInstanceWithClassName(DefaultForm::class, 'foo');
    }

    /**
     * Checks that when a static form object is fetched, it is injected in the
     * global FormZ configuration.
     *
     * @test
     */
    public function formIsInjectedInGlobalConfiguration()
    {
        $formObjectFactory = $this->getFormObjectFactory();
        $formObjectFactory->expects($this->once())
            ->method('getGlobalConfiguration');

        $formObjectFactory->getInstanceWithClassName(DefaultForm::class, 'foo');
        $formObjectFactory->getInstanceWithClassName(DefaultForm::class, 'foo');
        $formObjectFactory->getInstanceWithClassName(DefaultForm::class, 'foo');
    }

    /**
     * The proxy instance for a given form instance must always be the same.
     *
     * @test
     */
    public function formObjectProxyIsAlwaysTheSame()
    {
        $formObjectFactory = $this->getFormObjectFactory();
        $form = new DefaultForm;

        $formObjectFactory->registerFormInstance($form, 'foo');

        $proxy1 = $formObjectFactory->getProxy($form);
        $proxy2 = $formObjectFactory->getProxy($form);

        $this->assertSame($proxy1, $proxy2);
    }

    /**
     * @return FormObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFormObjectFactory()
    {
        /** @var FormObjectFactory|\PHPUnit_Framework_MockObject_MockObject $formObjectFactory */
        $formObjectFactory = $this->getMockBuilder(FormObjectFactory::class)
            ->setMethods(['buildStaticInstance', 'getCacheInstance', 'getGlobalConfiguration'])
            ->getMock();

        $static = $this->getMockBuilder(FormObjectStatic::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefinitionValidationResult'])
            ->getMock();

        $static->method('getDefinitionValidationResult')
            ->willReturn(new Result);

        $formObjectFactory->method('buildStaticInstance')
            ->willReturn($static);

        $formObjectFactory->method('getCacheInstance')
            ->willReturn($this->getCacheInstance()->reveal());

        $formObjectFactory->method('getGlobalConfiguration')
            ->willReturn($this->getMockBuilder(Configuration::class)->getMock());

        UnitTestContainer::get()->registerMockedInstance(FormObjectFactory::class, $formObjectFactory);

        return $formObjectFactory;
    }

    /**
     * @return ObjectProphecy
     */
    protected function getCacheInstance()
    {
        if (null === $this->cacheInstance) {
            /** @var FrontendInterface|ObjectProphecy $cacheInstance */
            $cacheInstance = $this->prophesize(FrontendInterface::class);

            $cacheInstance->has(Argument::type('string'))
                ->willReturn(false);

            $cacheInstance->set(Argument::type('string'), Argument::type(FormObjectStatic::class))
                ->will(
                    function ($arguments) use ($cacheInstance) {
                        $cacheInstance->has($arguments[0])
                            ->willReturn(true);

                        $cacheInstance->get($arguments[0])
                            ->willReturn($arguments[1]);
                    }
                );

            $this->cacheInstance = $cacheInstance;
        }

        return $this->cacheInstance;
    }
}
