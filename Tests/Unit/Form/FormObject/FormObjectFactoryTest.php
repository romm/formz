<?php

namespace Romm\Formz\Tests\Unit\Form\FormObject;

use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Configuration\ConfigurationFactory;
use Romm\Formz\Exceptions\ClassNotFoundException;
use Romm\Formz\Exceptions\InvalidArgumentTypeException;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Form\FormObject\FormObjectFactory;
use Romm\Formz\Form\FormObject\FormObjectProxy;
use Romm\Formz\Form\FormObject\FormObjectStatic;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Extbase\Error\Result;

class FormObjectFactoryTest extends AbstractUnitTest
{
    /**
     * Checks that a form object is created and returned.
     *
     * @test
     */
    public function formObjectFromClassNameIsCreated()
    {
        /** @var FormObjectFactory|\PHPUnit_Framework_MockObject_MockObject $formObjectFactory */
        $formObjectFactory = $this->getMockBuilder(FormObjectFactory::class)
            ->setMethods(['getStaticInstance'])
            ->getMock();

        $formObjectFactory->expects($this->once())
            ->method('getStaticInstance')
            ->willReturn(
                $this->getMockBuilder(FormObjectStatic::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );

        $formObject = $formObjectFactory->getInstanceWithClassName(DefaultForm::class, 'foo');

        $this->assertInstanceOf(FormObject::class, $formObject);

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
     * When getting a form object from a form instance and a given name,
     * memoization must be used: with the exact same form instance and name, the
     * exact same form object instance must be returned.
     *
     * @test
     */
    public function getInstanceFromFormInstanceUsesMemoization()
    {
        $configurationFactory = new ConfigurationFactory;
        $configurationFactory->injectTypoScriptService($this->getMockedTypoScriptService());

        /** @var FormObjectFactory|\PHPUnit_Framework_MockObject_MockObject $formObjectFactory */
        $formObjectFactory = $this->getMockBuilder(FormObjectFactory::class)
            ->setMethods(['getInstanceWithClassName'])
            ->getMock();

        $formObjectFactory->expects($this->exactly(2))
            ->method('getInstanceWithClassName')
            ->willReturnCallback(function () {
                $formObject = $this->getMockBuilder(FormObject::class)
                    ->disableOriginalConstructor()
                    ->setMethods(['setForm'])
                    ->getMock();

                $formObject->expects($this->once())
                    ->method('setForm');

                return $formObject;
            });

        $form = new DefaultForm;

        $formObject1 = $formObjectFactory->getInstanceWithFormInstance($form, 'foo');
        $formObject2 = $formObjectFactory->getInstanceWithFormInstance($form, 'foo');

        $this->assertSame($formObject1, $formObject2);

        $formObject3 = $formObjectFactory->getInstanceWithFormInstance($form);
        $formObject4 = $formObjectFactory->getInstanceWithFormInstance($form);

        $this->assertSame($formObject3, $formObject4);
        $this->assertNotSame($formObject1, $formObject3);
    }

    /**
     * The static form object instance of a given form class must be stored in
     * local cache.
     *
     * @test
     */
    public function formObjectStaticUsesMemoization()
    {
        /** @var FormObjectFactory|\PHPUnit_Framework_MockObject_MockObject $formObjectFactory */
        $formObjectFactory = $this->getMockBuilder(FormObjectFactory::class)
            ->setMethods(['buildStaticInstance', 'getGlobalConfiguration'])
            ->getMock();

        $formObjectFactory->expects($this->once())
            ->method('buildStaticInstance')
            ->willReturn($this->getDummyFormObjectStaticInstance());

        $formObjectFactory->expects($this->once())
            ->method('getGlobalConfiguration')
            ->willReturn($this->getConfigurationMock());

        $formObjectFactory->getInstanceWithClassName(DefaultForm::class, 'foo');
        $formObjectFactory->getInstanceWithClassName(DefaultForm::class, 'foo');
        $formObjectFactory->getInstanceWithClassName(DefaultForm::class, 'foo');

        unset($formObjectFactory);
    }

    /**
     * A form object instance must be stored in cache the first time it is
     * created, then it should be directly fetched from cache.
     *
     * @test
     */
    public function formObjectStaticIsStoredInCache()
    {
        /** @var FormObjectFactory|\PHPUnit_Framework_MockObject_MockObject $formObjectFactory1 */
        $formObjectFactory1 = $this->getMockBuilder(FormObjectFactory::class)
            ->setMethods(['buildStaticInstance', 'getGlobalConfiguration'])
            ->getMock();

        $formObjectFactory1->expects($this->once())
            ->method('buildStaticInstance')
            ->willReturn($this->getDummyFormObjectStaticInstance());

        $formObjectFactory1->expects($this->once())
            ->method('getGlobalConfiguration')
            ->willReturn($this->getConfigurationMock());

        /** @var FormObjectFactory|\PHPUnit_Framework_MockObject_MockObject $formObjectFactory2 */
        $formObjectFactory2 = $this->getMockBuilder(FormObjectFactory::class)
            ->setMethods(['buildStaticInstance', 'getGlobalConfiguration'])
            ->getMock();

        $formObjectFactory2->expects($this->never())
            ->method('buildStaticInstance')
            ->willReturn($this->getDummyFormObjectStaticInstance());

        $formObjectFactory2->expects($this->once())
            ->method('getGlobalConfiguration')
            ->willReturn($this->getConfigurationMock());

        $formObjectFactory1->getInstanceWithClassName(DefaultForm::class, 'foo');
        $formObjectFactory1->getInstanceWithClassName(DefaultForm::class, 'foo');
        $formObjectFactory2->getInstanceWithClassName(DefaultForm::class, 'foo');
        $formObjectFactory2->getInstanceWithClassName(DefaultForm::class, 'foo');

        unset($formObjectFactory1);
        unset($formObjectFactory2);
    }

    /**
     * Checks that when a static form object is fetched, it is injected in the
     * global FormZ configuration.
     *
     * @test
     */
    public function formIsInjectedInGlobalConfiguration()
    {
        /** @var FormObjectFactory|\PHPUnit_Framework_MockObject_MockObject $formObjectFactory */
        $formObjectFactory = $this->getMockBuilder(FormObjectFactory::class)
            ->setMethods(['buildStaticInstance', 'getGlobalConfiguration'])
            ->getMock();

        $formObjectFactory->expects($this->once())
            ->method('buildStaticInstance')
            ->willReturn($this->getDummyFormObjectStaticInstance());

        $formObjectFactory->expects($this->once())
            ->method('getGlobalConfiguration')
            ->willReturn($this->getConfigurationMock());

        $formObjectFactory->getInstanceWithClassName(DefaultForm::class, 'foo');
        $formObjectFactory->getInstanceWithClassName(DefaultForm::class, 'foo');
        $formObjectFactory->getInstanceWithClassName(DefaultForm::class, 'foo');
    }

    /**
     * The proxy instance for a given form instance must always be the same.
     *
     * @test
     */
    public function formObjectProxyUsesMemoization()
    {
        /** @var FormObjectFactory|\PHPUnit_Framework_MockObject_MockObject $formObjectFactory */
        $formObjectFactory = $this->getMockBuilder(FormObjectFactory::class)
            ->setMethods(['getNewProxyInstance'])
            ->getMock();

        $form1 = new DefaultForm;
        $form2 = new DefaultForm;

        $proxy1 = $this->getDummyFormObjectProxyInstance();
        $proxy2 = $this->getDummyFormObjectProxyInstance();

        $formObjectFactory->expects($this->at(0))
            ->method('getNewProxyInstance')
            ->with($form1)
            ->willReturn($proxy1);

        $formObjectFactory->expects($this->at(1))
            ->method('getNewProxyInstance')
            ->with($form2)
            ->willReturn($proxy2);

        $formObjectFactory->expects($this->exactly(2))
            ->method('getNewProxyInstance');

        $proxyResult1 = $formObjectFactory->getProxy($form1);
        $this->assertSame($proxy1, $proxyResult1);

        $proxyResult2 = $formObjectFactory->getProxy($form2);
        $this->assertSame($proxy2, $proxyResult2);

        $proxyResult3 = $formObjectFactory->getProxy($form1);
        $this->assertSame($proxyResult1, $proxyResult3);

        $proxyResult4 = $formObjectFactory->getProxy($form2);
        $this->assertSame($proxyResult2, $proxyResult4);
    }

    /**
     * @return FormObjectStatic|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDummyFormObjectStaticInstance()
    {
        $static = $this->getMockBuilder(FormObjectStatic::class)
            ->setMethods(['getDefinitionValidationResult'])
            ->disableOriginalConstructor()
            ->getMock();

        $static->method('getDefinitionValidationResult')
            ->willReturn(new Result);

        return $static;
    }

    /**
     * @return FormObjectProxy|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDummyFormObjectProxyInstance()
    {
        return $this->getMockBuilder(FormObjectProxy::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return Configuration|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getConfigurationMock()
    {
        return $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
