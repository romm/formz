<?php
namespace Romm\Formz\Tests\Unit\Form;

use Prophecy\Argument;
use Romm\ConfigurationObject\ConfigurationObjectInstance;
use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Form\FormObjectConfiguration;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Result;

class FormObjectConfigurationTest extends AbstractUnitTest
{
    /**
     * Checks that the configuration object is stored in cache, so it is not
     * built every time it is fetched.
     *
     * @test
     */
    public function configurationObjectIsStoredInCache()
    {
        /** @var FormObjectConfiguration|\PHPUnit_Framework_MockObject_MockObject $formObjectConfiguration */
        $formObjectConfiguration = $this->getMockBuilder(FormObjectConfiguration::class)
            ->setMethods(['buildConfigurationObject'])
            ->setConstructorArgs([$this->getDefaultFormObject(), []])
            ->getMock();

        $formObjectConfiguration->expects($this->once())
            ->method('buildConfigurationObject')
            ->willReturn($this->getConfigurationObjectInstance());

        for ($i = 0; $i < 3; $i++) {
            $formObjectConfiguration->getConfigurationObject();
        }

        /** @var FormObjectConfiguration|\PHPUnit_Framework_MockObject_MockObject $formObjectConfigurationBis */
        $formObjectConfigurationBis = $this->getMockBuilder(FormObjectConfiguration::class)
            ->setMethods(['buildConfigurationObject'])
            ->setConstructorArgs([$this->getDefaultFormObject(), []])
            ->getMock();

        $formObjectConfigurationBis->expects($this->never())
            ->method('buildConfigurationObject');

        for ($i = 0; $i < 3; $i++) {
            $formObjectConfigurationBis->getConfigurationObject();
        }

        unset($formObjectConfiguration);
        unset($formObjectConfigurationBis);
    }

    /**
     * If the built configuration object contains errors, it should not be
     * stored in persistent cache.
     *
     * @test
     */
    public function configurationObjectWithErrorsIsNotStoredInCache()
    {
        /** @var FormObjectConfiguration|\PHPUnit_Framework_MockObject_MockObject $formObjectConfiguration */
        $formObjectConfiguration = $this->getMockBuilder(FormObjectConfiguration::class)
            ->setMethods(['buildConfigurationObject', 'getCacheInstance'])
            ->setConstructorArgs([$this->getDefaultFormObject(), []])
            ->getMock();

        $configurationObjectInstance = $this->getConfigurationObjectInstance();
        $configurationObjectInstance->getValidationResult()->addError(new Error('foo', 42));

        $formObjectConfiguration->expects($this->once())
            ->method('buildConfigurationObject')
            ->willReturn($configurationObjectInstance);

        $cacheProphecy = $this->prophesize(FrontendInterface::class);

        $cacheProphecy->has(Argument::cetera())
            ->shouldBeCalled()
            ->will(function ($arguments) use ($cacheProphecy) {
                $cacheProphecy->set($arguments, Argument::cetera())
                    ->shouldNotBeCalled();
            });

        $formObjectConfiguration->method('getCacheInstance')
            ->willReturn($cacheProphecy->reveal());

        $formObjectConfiguration->getConfigurationObject();
    }

    /**
     * Checks that the configuration array is sanitized before creating the
     * configuration object: the fields that were not registered in the form
     * object must be deleted from the configuration array.
     *
     * @test
     */
    public function configurationArrayDeletesAdditionalFields()
    {
        $configurationArray = [
            'fields' => [
                'foo' => [],
                'bar' => []
            ]
        ];
        $sanitizedConfigurationArray = [
            'fields' => ['foo' => []]
        ];

        /** @var FormObjectConfiguration|\PHPUnit_Framework_MockObject_MockObject $formObjectConfiguration */
        $formObjectConfiguration = $this->getMockBuilder(FormObjectConfiguration::class)
            ->setMethods(['getConfigurationObjectInstance'])
            ->setConstructorArgs([$this->getDefaultFormObject(), $configurationArray])
            ->getMock();

        $formObjectConfiguration->expects($this->once())
            ->method('getConfigurationObjectInstance')
            ->with($sanitizedConfigurationArray)
            ->willReturn($this->getConfigurationObjectInstance());

        $formObjectConfiguration->getConfigurationObject();
    }

    /**
     * If the hash of the form object changes, the configuration object should
     * be rebuilt.
     *
     * @test
     */
    public function configurationObjectIsRebuiltWhenFormObjectHashChanges()
    {
        $formObject = $this->getDefaultFormObject();

        /** @var FormObjectConfiguration|\PHPUnit_Framework_MockObject_MockObject $formObjectConfiguration */
        $formObjectConfiguration = $this->getMockBuilder(FormObjectConfiguration::class)
            ->setMethods(['getConfigurationObjectFromCache'])
            ->setConstructorArgs([$formObject, []])
            ->getMock();

        $formObjectConfiguration->expects($spy = $this->exactly(2))
            ->method('getConfigurationObjectFromCache')
            ->willReturn($this->getConfigurationObjectInstance());

        $formObjectConfiguration->getConfigurationObject();
        $formObjectConfiguration->getConfigurationObject();
        $formObjectConfiguration->getConfigurationObject();

        $this->assertEquals(1, $spy->getInvocationCount());

        $formObject->addProperty('baz');

        $formObjectConfiguration->getConfigurationObject();
        $formObjectConfiguration->getConfigurationObject();
        $formObjectConfiguration->getConfigurationObject();

        $this->assertEquals(2, $spy->getInvocationCount());
    }

    /**
     * If the hash of the form object changes, the validation result should
     * be rebuilt.
     *
     * @test
     */
    public function validationResultIsRebuiltWhenFormObjectHashChanges()
    {
        $formObject = $this->getDefaultFormObject();

        /** @var FormObjectConfiguration|\PHPUnit_Framework_MockObject_MockObject $formObjectConfiguration */
        $formObjectConfiguration = $this->getMockBuilder(FormObjectConfiguration::class)
            ->setMethods(['getConfigurationObjectFromCache', 'refreshConfigurationValidationResult'])
            ->setConstructorArgs([$formObject, []])
            ->getMock();

        $formObjectConfiguration->method('getConfigurationObjectFromCache')
            ->willReturn($this->getConfigurationObjectInstance());

        $formObjectConfiguration->expects($spy = $this->exactly(2))
            ->method('refreshConfigurationValidationResult')
            ->willReturn(new Result);

        $formObjectConfiguration->getConfigurationValidationResult();
        $formObjectConfiguration->getConfigurationValidationResult();
        $formObjectConfiguration->getConfigurationValidationResult();

        $this->assertEquals(1, $spy->getInvocationCount());

        $formObject->addProperty('baz');

        $formObjectConfiguration->getConfigurationValidationResult();
        $formObjectConfiguration->getConfigurationValidationResult();
        $formObjectConfiguration->getConfigurationValidationResult();

        $this->assertEquals(2, $spy->getInvocationCount());
    }

    /**
     * @return ConfigurationObjectInstance
     */
    protected function getConfigurationObjectInstance()
    {
        $formzConfiguration = new Configuration;
        $result = new Result;

        return new ConfigurationObjectInstance($formzConfiguration, $result);
    }
}
