<?php
namespace Romm\Formz\Tests\Unit;

use Romm\Formz\Configuration\ConfigurationFactory;
use Romm\Formz\Configuration\ConfigurationServicesUtility;
use Romm\Formz\Core\Core;
use Romm\Formz\Form\FormObjectFactory;
use Romm\Formz\Utility\TypoScriptUtility;
use TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend;
use TYPO3\CMS\Core\Cache\CacheFactory;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

trait FormzUnitTestUtility
{

    /**
     * @var Core|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formzCoreMock;

    /**
     * Initializes correctly this extension `Core` class to be able to work
     * correctly in unit tests.
     */
    private function setUpFormzCore()
    {
        $this->formzCoreMock = $this->getMock(Core::class, ['dummy']);
        $this->formzCoreMock->injectObjectManager($this->getFormzObjectManagerMock());
        $this->formzCoreMock->injectTypoScriptUtility(new TypoScriptUtility);
        $this->formzCoreMock->injectConfigurationFactory(new ConfigurationFactory);
        $this->formzCoreMock->injectFormObjectFactory(new FormObjectFactory());

        $reflectedCore = new \ReflectionClass(Core::class);
        $objectManagerProperty = $reflectedCore->getProperty('instance');
        $objectManagerProperty->setAccessible(true);
        $objectManagerProperty->setValue($this->formzCoreMock);
    }

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

        $this->formzCoreMock->setCacheInstance($cacheInstance);
    }

    /**
     * Returns a mocked instance of the Extbase `ObjectManager`. Will allow the
     * main function `get()` to work properly during the tests.
     *
     * @return ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getFormzObjectManagerMock()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $objectManagerMock */
        $objectManagerMock = $this->getMock(ObjectManagerInterface::class);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->will(
                $this->returnCallback(
                    function () {
                        $arguments = func_get_args();

                        $reflectionClass = new \ReflectionClass(array_shift($arguments));
                        if (empty($arguments)) {
                            $instance = $reflectionClass->newInstance();
                        } else {
                            $instance = $reflectionClass->newInstanceArgs($arguments);
                        }

                        return $instance;
                    }
                )
            );

        return $objectManagerMock;
    }
}
