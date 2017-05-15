<?php

namespace Romm\Formz\Tests\Unit\Configuration;

use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Configuration\Settings\Settings;
use Romm\Formz\Configuration\View\View;
use Romm\Formz\Service\CacheService;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\UnitTestContainer;

class ConfigurationTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function initializationDoneProperly()
    {
        $configuration = new Configuration;

        $this->assertInstanceOf(Settings::class, $configuration->getSettings());
        $this->assertInstanceOf(View::class, $configuration->getView());
    }

    /**
     * @test
     */
    public function hashIsEqualsWithSameConfiguration()
    {
        $configuration1 = new Configuration;
        $configuration1->getView()->setPartialRootPath(10, 'foo-bar');

        $configuration2 = new Configuration;
        $configuration2->getView()->setPartialRootPath(10, 'foo-bar');

        $configuration3 = new Configuration;
        $configuration3->getView()->setPartialRootPath(10, 'bar-baz');

        $this->assertSame($configuration1->getHash(), $configuration2->getHash());
        $this->assertNotSame($configuration1->getHash(), $configuration3->getHash());
    }

    /**
     * @test
     */
    public function hashIsCalculatedOnce()
    {
        /** @var Configuration|\PHPUnit_Framework_MockObject_MockObject $configuration */
        $configuration = $this->getMockBuilder(Configuration::class)
            ->setMethods(['calculateHash'])
            ->getMock();

        $configuration->expects($this->once())
            ->method('calculateHash')
            ->willReturn('foo');

        $configuration->getHash();
        $configuration->getHash();
        $configuration->getHash();
    }

    /**
     * Checks that the static function `getConfigurationObjectServices` needed
     * by the `configuration_object` API returns a valid class.
     *
     * @test
     */
    public function configurationObjectServicesAreValid()
    {
        $cacheServiceMock = $this->getMockBuilder(CacheService::class)
            ->setMethods(['getBackendCache'])
            ->getMock();

        $cacheServiceMock->method('getBackendCache')
            ->willReturn('foo');

        UnitTestContainer::get()->registerMockedInstance(CacheService::class, $cacheServiceMock);

        $serviceFactory = Configuration::getConfigurationObjectServices();

        $this->assertInstanceOf(ServiceFactory::class, $serviceFactory);

        unset($serviceFactory);
    }
}
