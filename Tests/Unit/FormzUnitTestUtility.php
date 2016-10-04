<?php
namespace Romm\Formz\Tests\Unit;

use Romm\Formz\Configuration\ConfigurationServicesUtility;
use Romm\Formz\Core\Core;
use TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend;
use TYPO3\CMS\Core\Cache\CacheFactory;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;

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
        $property = $reflectedCore->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue($mock);
    }

    /**
     * Inject a special type of cache that will work with the unit tests suit.
     */
    public function injectTransientMemoryCacheInCore()
    {
        $cacheManager = new CacheManager();
        $cacheFactory = new CacheFactory('foo', $cacheManager);
        $cacheInstance = $cacheFactory->create('foo', VariableFrontend::class, TransientMemoryBackend::class);

        $reflectedCore = new \ReflectionClass(Core::class);
        $property = $reflectedCore->getProperty('cacheInstance');
        $property->setAccessible(true);
        $property->setValue($cacheInstance);
    }
}
