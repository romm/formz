<?php
namespace Romm\Formz\Tests\Unit\Configuration;

use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Service\CacheService;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\UnitTestContainer;

class ConfigurationTest extends AbstractUnitTest
{
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

    /**
     * Checks that a form can be added properly to the list.
     *
     * @test
     */
    public function formCanBeAdded()
    {
        $configuration = new Configuration();
        $formObjectStatic = $this->getDefaultFormObjectStatic();

        $className = $formObjectStatic->getClassName();

        $this->assertFalse($configuration->hasForm($className));

        $configuration->addForm($formObjectStatic);

        $this->assertTrue($configuration->hasForm($className));
        $this->assertSame($formObjectStatic, $configuration->getForm($className));

        unset($configuration);
        unset($formObject);
    }

    /**
     * Adding two forms with the same name and class name must throw an
     * exception.
     *
     * @test
     */
    public function addingSameFormTwoTimesThrowsException()
    {
        $configuration = new Configuration();
        $formObjectStatic = $this->getDefaultFormObjectStatic();
        $formObjectStatic2 = $this->getDefaultFormObjectStatic();

        $this->setExpectedException(DuplicateEntryException::class);

        $configuration->addForm($formObjectStatic);
        $configuration->addForm($formObjectStatic2);

        unset($configuration);
        unset($formObject);
        unset($formObject2);
    }
}
