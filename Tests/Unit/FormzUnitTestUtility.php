<?php
namespace Romm\Formz\Tests\Unit;

use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\Condition\ConditionFactory;
use Romm\Formz\Configuration\ConfigurationFactory;
use Romm\Formz\Core\Core;
use Romm\Formz\Form\FormObject;
use Romm\Formz\Service\CacheService;
use Romm\Formz\Service\TypoScriptService;
use Romm\Formz\Tests\Fixture\Configuration\FormzConfiguration;
use TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend;
use TYPO3\CMS\Core\Cache\CacheFactory;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Container\Container;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\EnvironmentService;
use TYPO3\CMS\Extbase\Service\TypoScriptService as ExtbaseTypoScriptService;
use TYPO3\CMS\Extbase\Utility\ArrayUtility;

trait FormzUnitTestUtility
{
    /**
     * @var EnvironmentService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockedEnvironmentService;

    /**
     * @var TypoScriptService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockedTypoScriptService;

    /**
     * @var Core|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formzCoreMock;

    /**
     * @var array
     */
    protected $formzConfiguration = [];

    /**
     * @var array
     */
    protected $formConfiguration = [];

    /**
     * @var array
     */
    protected $extensionConfiguration = [];

    /**
     * @var bool
     */
    private $frontendEnvironment = true;

    /**
     * Can be used in the `setUp()` function of every unit test.
     */
    protected function formzSetUp()
    {
        $this->initializeConfigurationObjectTestServices();

        $this->setUpFormzCoreMock();
        $this->overrideExtbaseContainer();
        $this->changeReflectionCache();
        $this->injectDependenciesInFormzCore();

        ConditionFactory::get()->registerDefaultConditions();
    }

    /**
     * Can be used in the `tearDown()` function of every unit test.
     */
    protected function formzTearDown()
    {
        // Reset asset handler factory instances.
        $reflectedClass = new \ReflectionClass(AssetHandlerFactory::class);
        $objectManagerProperty = $reflectedClass->getProperty('factoryInstances');
        $objectManagerProperty->setAccessible(true);
        $objectManagerProperty->setValue([]);
        $objectManagerProperty->setAccessible(false);

        // Reset configuration factory instances.
        $configurationFactory = Core::instantiate(ConfigurationFactory::class);
        $reflectedObject = new \ReflectionObject($configurationFactory);
        $objectManagerProperty = $reflectedObject->getProperty('instances');
        $objectManagerProperty->setAccessible(true);
        $objectManagerProperty->setValue($configurationFactory, []);
        $objectManagerProperty->setAccessible(false);

        UnitTestContainer::get()->resetInstances();
    }

    /**
     * @return FormObject
     */
    protected function getFormObject()
    {
        $formObject = new FormObject(
            AbstractUnitTest::FORM_OBJECT_DEFAULT_CLASS_NAME,
            AbstractUnitTest::FORM_OBJECT_DEFAULT_NAME
        );

        $formObject->injectConfigurationFactory(Core::instantiate(ConfigurationFactory::class));

        return $formObject;
    }

    /**
     * This function will force the type of cache of `extbase_object` to
     * `TransientMemoryBackend` instead of something like database.
     */
    protected function changeReflectionCache()
    {
        /** @var CacheManager $cacheManager */
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);

        $cacheManager->setCacheConfigurations([
            'extbase_object' => [
                'frontend' => VariableFrontend::class,
                'backend'  => TransientMemoryBackend::class
            ]
        ]);
    }

    /**
     * Overrides Extbase default container to be more flexible.
     *
     * @see UnitTestContainer
     */
    protected function overrideExtbaseContainer()
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][Container::class]['className'] = UnitTestContainer::class;

        UnitTestContainer::get()->registerMockedInstance(TypoScriptService::class, $this->getMockedTypoScriptService());
        UnitTestContainer::get()->registerMockedInstance(EnvironmentService::class, $this->getMockedEnvironmentService());
    }

    /**
     * Initializes correctly this extension `Core` class to be able to work
     * correctly in unit tests.
     */
    protected function setUpFormzCoreMock()
    {
        // Injecting the mocked instance in the core.
        $this->formzCoreMock = $this->getMock(
            Core::class,
            ['translate', 'getFullExtensionConfiguration', 'getExtensionRelativePath']
        );

        $reflectedCore = new \ReflectionClass(Core::class);
        $objectManagerProperty = $reflectedCore->getProperty('instance');
        $objectManagerProperty->setAccessible(true);
        $objectManagerProperty->setValue($this->formzCoreMock);
    }

    protected function injectDependenciesInFormzCore()
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->formzCoreMock->injectObjectManager($objectManager);
        $this->formzCoreMock->injectTypoScriptService($this->getMockedTypoScriptService());
        $this->formzCoreMock->injectEnvironmentService($this->getMockedEnvironmentService());

        $this->injectTransientMemoryCacheInFormzCore();

        $this->setFormzConfiguration(FormzConfiguration::getDefaultConfiguration());

        /*
         * Mocking the translate function, to avoid the fatal error due to TYPO3
         * core trying to get the localization data in database cache.
         */
        $this->formzCoreMock->method('translate')
            ->will(
                $this->returnCallback(
                    function ($key, $extension) {
                        return 'LLL:' . $extension . ':' . $key;
                    }
                )
            );

        /*
         * Will return a configuration that can be manipulated during tests.
         */
        $this->formzCoreMock->method('getFullExtensionConfiguration')
            ->willReturnCallback(function () {
                return $this->extensionConfiguration;
            });

        /*
         * The relative path can't be fetched during unit tests: we force a
         * static value.
         */
        $this->formzCoreMock->method('getExtensionRelativePath')
            ->will(
                $this->returnCallback(
                    function ($path = null) {
                        $relativePath = '/tmp/formz/';

                        return (null !== $path)
                            ? $relativePath . $path
                            : $relativePath;
                    }
                )
            );
    }

    /**
     * Makes the mocked environment service from the core class be in frontend
     * environment.
     */
    protected function setFrontendEnvironment()
    {
        $this->frontendEnvironment = true;
    }

    /**
     * Makes the mocked environment service from the core class be in backend
     * environment.
     */
    protected function setBackendEnvironment()
    {
        $this->frontendEnvironment = false;
    }

    /**
     * @param array $formzConfiguration
     */
    protected function setFormzConfiguration(array $formzConfiguration)
    {
        $this->formzConfiguration = $formzConfiguration;
    }

    /**
     * Sets the array configuration for a given form class name.
     *
     * @param string $className
     * @param array  $configuration
     */
    protected function setFormConfigurationFromClassName($className, array $configuration)
    {
        $this->formConfiguration[$className] = $configuration;
    }

    /**
     * @param array $extensionConfiguration
     */
    protected function setExtensionConfiguration(array $extensionConfiguration)
    {
        $this->extensionConfiguration = $extensionConfiguration;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    protected function setExtensionConfigurationValue($key, $value)
    {
        $this->extensionConfiguration[$key] = $value;
    }

    /**
     * Inject a special type of cache that will work with the unit tests suit.
     */
    private function injectTransientMemoryCacheInFormzCore()
    {
        $cacheFactory = new CacheFactory('foo', new CacheManager);
        $cacheInstance = $cacheFactory->create('foo', VariableFrontend::class, TransientMemoryBackend::class);

        CacheService::get()->setCacheInstance($cacheInstance);
    }

    /**
     * The mocked service allows unit tests to manipulate the current
     * environment easily thanks to the functions `setFrontendEnvironment()` and
     * `setBackendEnvironment()`.
     *
     * @return EnvironmentService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockedEnvironmentService()
    {
        if (null === $this->mockedEnvironmentService) {
            $this->mockedEnvironmentService = $this->getMock(EnvironmentService::class, ['isEnvironmentInFrontendMode', 'isEnvironmentInBackendMode']);

            $this->mockedEnvironmentService->method('isEnvironmentInFrontendMode')
                ->willReturnCallback(function () {
                    return $this->frontendEnvironment;
                });

            $this->mockedEnvironmentService->method('isEnvironmentInBackendMode')
                ->willReturnCallback(function () {
                    return !$this->frontendEnvironment;
                });
        }

        return $this->mockedEnvironmentService;
    }

    /**
     *
     * This function will mock the `TypoScriptService` class to return a
     * custom configuration array.
     *
     * @return TypoScriptService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockedTypoScriptService()
    {
        if (null === $this->mockedTypoScriptService) {
            $this->mockedTypoScriptService = $this->getMock(TypoScriptService::class, ['getFrontendTypoScriptConfiguration', 'getBackendTypoScriptConfiguration']);

            $configurationCallBack = function () {
                $configuration = ArrayUtility::setValueByPath(
                    $this->formzConfiguration,
                    'config.tx_formz.forms',
                    $this->formConfiguration
                );

                return $configuration;
            };

            $this->mockedTypoScriptService->method('getFrontendTypoScriptConfiguration')
                ->willReturnCallback($configurationCallBack);

            $this->mockedTypoScriptService->method('getBackendTypoScriptConfiguration')
                ->willReturnCallback($configurationCallBack);

            $this->mockedTypoScriptService->injectEnvironmentService($this->getMockedEnvironmentService());
            $this->mockedTypoScriptService->injectTypoScriptService(new ExtbaseTypoScriptService);
        }

        return $this->mockedTypoScriptService;
    }
}
