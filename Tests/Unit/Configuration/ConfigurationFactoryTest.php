<?php
namespace Romm\Formz\Tests\Unit\Configuration;

use Romm\ConfigurationObject\ConfigurationObjectInstance;
use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Configuration\ConfigurationFactory;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

class ConfigurationFactoryTest extends AbstractUnitTest
{

    /**
     * Checks that the factory can build and return the main FormZ configuration
     * instance.
     *
     * @test
     */
    public function canGetRootConfiguration()
    {
        $configurationFactory = new ConfigurationFactory;
        $configurationFactory->injectTypoScriptService($this->getMockedTypoScriptService());
        $configurationFactory->injectSignalSlotDispatcher(new Dispatcher);
        $rootConfiguration = $configurationFactory->getRootConfiguration();

        $this->assertInstanceOf(ConfigurationObjectInstance::class, $rootConfiguration);
        $this->assertInstanceOf(Configuration::class, $rootConfiguration->getObject(true));

        unset($configurationFactory);
    }

    /**
     * Local cache must be used when getting the formz configuration, as it may
     * be fetched many times during a script execution.
     *
     * @test
     */
    public function localCacheIsUsedForRootConfiguration()
    {
        /** @var ConfigurationFactory|\PHPUnit_Framework_MockObject_MockObject $configurationFactory */
        $configurationFactory = $this->getMockBuilder(ConfigurationFactory::class)
            ->setMethods(['getRootConfigurationFromCache'])
            ->getMock();
        $configurationFactory->injectTypoScriptService($this->getMockedTypoScriptService());

        $configurationInstance = new Configuration;
        $configurationResult = new Result;
        $configurationObjectInstance = new ConfigurationObjectInstance($configurationInstance, $configurationResult);

        $configurationFactory->expects($this->once())
            ->method('getRootConfigurationFromCache')
            ->willReturn($configurationObjectInstance);

        for ($i = 0; $i < 3; $i++) {
            $configurationObjectInstanceFromFactory = $configurationFactory->getRootConfiguration();
            $this->assertSame($configurationObjectInstanceFromFactory, $configurationObjectInstance);
        }

        unset($configurationInstance);
        unset($configurationFactory);
    }

    /**
     * Checking that the formz configuration is not stored in cache when errors
     * were found during the configuration being built.
     *
     * @test
     */
    public function builtRootConfigurationIsNotStoredInSystemCacheWhenItDoesHaveErrors()
    {
        /** @var ConfigurationFactory|\PHPUnit_Framework_MockObject_MockObject $configurationFactory */
        $configurationFactory = $this->getMockBuilder(ConfigurationFactory::class)
            ->setMethods(['buildRootConfiguration'])
            ->getMock();
        $configurationFactory->injectTypoScriptService($this->getMockedTypoScriptService());

        /** @var ConfigurationFactory|\PHPUnit_Framework_MockObject_MockObject $configurationFactory2 */
        $configurationFactory2 = $this->getMockBuilder(ConfigurationFactory::class)
            ->setMethods(['buildRootConfiguration'])
            ->getMock();
        $configurationFactory2->injectTypoScriptService($this->getMockedTypoScriptService());

        $configurationInstance = new Configuration;
        $configurationResult = new Result;
        $configurationResult->addError(new Error('foo', 1337));
        $configurationObjectInstance = new ConfigurationObjectInstance($configurationInstance, $configurationResult);
        $configurationObjectInstance->refreshValidationResult();

        $configurationFactory->expects($this->exactly(1))
            ->method('buildRootConfiguration')
            ->willReturn($configurationObjectInstance);

        $configurationFactory2->expects($this->exactly(1))
            ->method('buildRootConfiguration')
            ->willReturn($configurationObjectInstance);

        $configurationObjectInstanceFromFactory = $configurationFactory->getRootConfiguration();
        $this->assertSame($configurationObjectInstanceFromFactory, $configurationObjectInstance);

        $configurationObjectInstanceFromFactory = $configurationFactory2->getRootConfiguration();
        $this->assertSame($configurationObjectInstanceFromFactory, $configurationObjectInstance);

        unset($configurationInstance);
        unset($configurationFactory);
    }

    /**
     * Checking that the formz configuration is stored in cache when no errors
     * were found during the configuration being built.
     *
     * @test
     */
    public function builtRootConfigurationIsStoredInSystemCacheWhenItDoesNotHaveErrors()
    {
        /** @var ConfigurationFactory|\PHPUnit_Framework_MockObject_MockObject $configurationFactory */
        $configurationFactory = $this->getMockBuilder(ConfigurationFactory::class)
            ->setMethods(['buildRootConfiguration'])
            ->getMock();
        $configurationFactory->injectTypoScriptService($this->getMockedTypoScriptService());

        /** @var ConfigurationFactory|\PHPUnit_Framework_MockObject_MockObject $configurationFactory2 */
        $configurationFactory2 = $this->getMockBuilder(ConfigurationFactory::class)
            ->setMethods(['buildRootConfiguration'])
            ->getMock();
        $configurationFactory2->injectTypoScriptService($this->getMockedTypoScriptService());

        $configurationInstance = new Configuration;
        $configurationResult = new Result;
        $configurationObjectInstance = new ConfigurationObjectInstance($configurationInstance, $configurationResult);
        $configurationObjectInstance->refreshValidationResult();

        $configurationFactory->expects($this->exactly(1))
            ->method('buildRootConfiguration')
            ->willReturn($configurationObjectInstance);

        $configurationFactory2->expects($this->never())
            ->method('buildRootConfiguration');

        $configurationObjectInstanceFromFactory = $configurationFactory->getRootConfiguration();
        $this->assertSame($configurationObjectInstanceFromFactory, $configurationObjectInstance);

        $configurationFactory2->getRootConfiguration();
        $this->assertEquals(serialize($configurationObjectInstanceFromFactory), serialize($configurationObjectInstance));

        unset($configurationInstance);
        unset($configurationFactory);
    }
}
