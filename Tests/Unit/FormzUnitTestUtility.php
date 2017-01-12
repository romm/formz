<?php
namespace Romm\Formz\Tests\Unit;

use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\Condition\ConditionFactory;
use Romm\Formz\Configuration\ConfigurationFactory;
use Romm\Formz\Core\Core;
use Romm\Formz\Form\FormObject;
use Romm\Formz\Form\FormObjectFactory;
use Romm\Formz\Tests\Fixture\Configuration\FormzConfiguration;
use Romm\Formz\Utility\TypoScriptUtility;
use TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend;
use TYPO3\CMS\Core\Cache\CacheFactory;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Service\EnvironmentService;
use TYPO3\CMS\Extbase\Service\TypoScriptService;
use TYPO3\CMS\Extbase\Utility\ArrayUtility;

trait FormzUnitTestUtility
{

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
     * Can be used in the `setUp()` function of every unit test.
     */
    protected function formzSetUp()
    {
        $this->initializeConfigurationObjectTestServices();
        $this->setUpFormzCore();

        ConditionFactory::get()->registerDefaultConditions();
    }

    /**
     * Can be used in the `tearDown()` function of every unit test.
     */
    protected function formzTearDown()
    {
        // Reset asset handler factory instances.
        $reflectedCore = new \ReflectionClass(AssetHandlerFactory::class);
        $objectManagerProperty = $reflectedCore->getProperty('factoryInstances');
        $objectManagerProperty->setAccessible(true);
        $objectManagerProperty->setValue([]);
        $objectManagerProperty->setAccessible(false);
    }

    /**
     * @return FormObject
     */
    protected function getFormObject()
    {
        return new FormObject(
            AbstractUnitTest::FORM_OBJECT_DEFAULT_CLASS_NAME,
            AbstractUnitTest::FORM_OBJECT_DEFAULT_NAME
        );
    }

    /**
     * Initializes correctly this extension `Core` class to be able to work
     * correctly in unit tests.
     */
    private function setUpFormzCore()
    {
        $this->formzCoreMock = $this->getMock(
            Core::class,
            ['translate', 'getFullExtensionConfiguration', 'getExtensionRelativePath']
        );

        $this->formzCoreMock->injectObjectManager($this->getFormzObjectManagerMock());
        $this->formzCoreMock->injectTypoScriptUtility(new TypoScriptUtility);
        $this->formzCoreMock->injectConfigurationFactory(new ConfigurationFactory);
        $this->formzCoreMock->injectFormObjectFactory(new FormObjectFactory());

        $this->injectTransientMemoryCacheInFormzCore();

        $this->setFormzConfiguration(FormzConfiguration::getDefaultConfiguration());
        $this->injectMockedTypoScriptUtilityInFormzCore();

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

        // Injecting the mocked instance in the core.
        $reflectedCore = new \ReflectionClass(Core::class);
        $objectManagerProperty = $reflectedCore->getProperty('instance');
        $objectManagerProperty->setAccessible(true);
        $objectManagerProperty->setValue($this->formzCoreMock);
    }

    /**
     * Inject a special type of cache that will work with the unit tests suit.
     */
    private function injectTransientMemoryCacheInFormzCore()
    {
        $cacheManager = new CacheManager;
        $cacheFactory = new CacheFactory('foo', $cacheManager);
        $cacheInstance = $cacheFactory->create('foo', VariableFrontend::class, TransientMemoryBackend::class);

        $this->formzCoreMock->setCacheInstance($cacheInstance);
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
     * This function will mock the `TypoScriptUtility` class to return a
     * custom configuration array.
     */
    private function injectMockedTypoScriptUtilityInFormzCore()
    {
        /** @var TypoScriptUtility|\PHPUnit_Framework_MockObject_MockObject $typoScriptUtilityMock */
        $typoScriptUtilityMock = $this->getMock(TypoScriptUtility::class, ['getConfiguration']);
        $typoScriptUtilityMock->expects($this->any())
            ->method('getConfiguration')
            ->will(
                $this->returnCallback(
                    function () {
                        $configuration = ArrayUtility::setValueByPath(
                            $this->formzConfiguration,
                            'config.tx_formz.forms',
                            $this->formConfiguration
                        );

                        return $configuration;
                    }
                )
            );

        $typoScriptUtilityMock->injectEnvironmentService(new EnvironmentService);
        $typoScriptUtilityMock->injectTypoScriptService(new TypoScriptService);

        $this->formzCoreMock->injectTypoScriptUtility($typoScriptUtilityMock);
    }

    /**
     * Returns a mocked instance of the Extbase `ObjectManager`. Will allow the
     * main function `get()` to work properly during the tests.
     *
     * @return ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getFormzObjectManagerMock()
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
