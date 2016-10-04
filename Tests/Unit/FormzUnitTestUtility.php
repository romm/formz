<?php
namespace Romm\Formz\Tests\Unit;

use Romm\Formz\Configuration\ConfigurationServicesUtility;

trait FormzUnitTestUtility
{

    /**
     * Will force the instance of `ConfigurationServicesUtility` to be mocked
     * and not inject the cache service.
     */
    public function injectMockedConfigurationServicesUtility()
    {
        $mock = $this->getMock(ConfigurationServicesUtility::class, ['addCacheServiceToServiceFactory']);

        $reflectedCore = new \ReflectionClass(ConfigurationServicesUtility::class);
        $objectManagerProperty = $reflectedCore->getProperty('instance');
        $objectManagerProperty->setAccessible(true);
        $objectManagerProperty->setValue($mock);
    }
}
