<?php
namespace Romm\Formz\Tests\Unit;

use Romm\ConfigurationObject\ConfigurationObjectInstance;
use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\Condition\ConditionFactory;
use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Configuration\ConfigurationFactory;
use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Configuration\Form\Form;
use Romm\Formz\Configuration\View\Classes\ViewClass;
use Romm\Formz\Core\Core;
use Romm\Formz\Form\FormObject;
use Romm\Formz\Service\CacheService;
use Romm\Formz\Service\ContextService;
use Romm\Formz\Service\ExtensionService;
use Romm\Formz\Service\FacadeService;
use Romm\Formz\Service\TypoScriptService;
use Romm\Formz\Tests\Fixture\Configuration\FormzConfiguration;
use TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend;
use TYPO3\CMS\Core\Cache\CacheFactory;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Package\Package;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Object\Container\Container;
use TYPO3\CMS\Extbase\Service\EnvironmentService;
use TYPO3\CMS\Extbase\Service\TypoScriptService as ExtbaseTypoScriptService;

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
     * @var array
     */
    protected $formzConfiguration = [];

    /**
     * @var array
     */
    protected $extensionConfiguration = [];

    /**
     * @var bool
     */
    private $frontendEnvironment = true;

    /**
     * @var PackageManager
     */
    private $packageManagerBackUp;

    /**
     * Can be used in the `setUp()` function of every unit test.
     */
    protected function formzSetUp()
    {
        $this->injectAllDependencies();

        ConditionFactory::get()->registerDefaultConditions();
    }

    /**
     * Function to inject every dependency needed during a unit test.
     */
    protected function injectAllDependencies()
    {
        /*
        * The function below is part of the Configuration Object API. It must
        * be called in order to use the API during unit testing.
        */
        $this->initializeConfigurationObjectTestServices();

        FacadeService::get()->reset();

        /*
         * Will return a configuration that can be manipulated during tests.
         */
        $this->formzConfiguration = FormzConfiguration::getDefaultConfiguration();

        $this->backUpPackageManager();
        $this->overrideExtbaseContainer();
        $this->changeReflectionCache();
        $this->injectTransientMemoryCacheInFormzCore();
        $this->setUpExtensionServiceMock();
        $this->setUpContextService();
    }

    /**
     * Can be used in the `tearDown()` function of every unit test.
     */
    protected function formzTearDown()
    {
        $this->restorePackageManager();

        // Reset asset handler factory instances.
        $reflectedClass = new \ReflectionClass(AssetHandlerFactory::class);
        $property = $reflectedClass->getProperty('factoryInstances');
        $property->setAccessible(true);
        $property->setValue([]);
        $property->setAccessible(false);

        // Reset configuration factory instances.
        $configurationFactory = Core::instantiate(ConfigurationFactory::class);
        $reflectedObject = new \ReflectionObject($configurationFactory);
        $property = $reflectedObject->getProperty('instances');
        $property->setAccessible(true);
        $property->setValue($configurationFactory, []);
        $property->setAccessible(false);

        UnitTestContainer::get()->resetInstances();
    }

    /**
     * @return FormObject
     */
    protected function getDefaultFormObject()
    {
        return $this->createFormObject(['foo']);
    }

    /**
     * @return FormObject
     */
    protected function getExtendedFormObject()
    {
        return $this->createFormObject(['foo', 'bar']);
    }

    /**
     * @param array $fields
     * @return FormObject
     */
    protected function createFormObject(array $fields = [])
    {
        /** @var FormObject|\PHPUnit_Framework_MockObject_MockObject $formObject */
        $formObject = $this->getMockBuilderWrap(FormObject::class)
            ->setMethods(['getConfiguration', 'getConfigurationValidationResult', 'setUpConfiguration'])
            ->setConstructorArgs([
                AbstractUnitTest::FORM_OBJECT_DEFAULT_CLASS_NAME,
                AbstractUnitTest::FORM_OBJECT_DEFAULT_NAME,
                []
            ])
            ->getMock();

        $formConfiguration = new Form;

        foreach ($fields as $fieldName) {
            $field = new Field;
            $field->setFieldName($fieldName);
            $field->setParents([$formConfiguration]);
            $formConfiguration->addField($field);

            $formObject->addProperty($fieldName);
        }

        $configurationObjectInstance = new ConfigurationObjectInstance($formConfiguration, new Result);
        $configurationObjectInstance->setValidationResult(new Result);

        $formzConfiguration = new Configuration();

        $formzConfiguration->getSettings()->getDefaultFieldSettings()->setFeedbackContainerSelector('[fz-field-feedback-container="#FIELD#"]');
        $formzConfiguration->getSettings()->getDefaultFieldSettings()->setFieldContainerSelector('[fz-field-container="#FIELD#"]');

        $errors = new ViewClass;
        $errors->setItem('foo', 'foo');
        /** @noinspection PhpUndefinedMethodInspection */
        $formzConfiguration->getView()->getClasses()->setErrors($errors);

        $valid = new ViewClass;
        $valid->setItem('bar', 'bar');
        /** @noinspection PhpUndefinedMethodInspection */
        $formzConfiguration->getView()->getClasses()->setValid($valid);

        $formConfiguration->setParents([$formzConfiguration]);

        $formObject->method('getConfiguration')
            ->willReturn($configurationObjectInstance->getObject(true));

        $formObject->method('getConfigurationValidationResult')
            ->willReturn(new Result);

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
     * Overrides the default package manager.
     */
    protected function setUpPackageManagerMock()
    {
        /** @var Package|\PHPUnit_Framework_MockObject_MockObject $package */
        $package = $this->getMockBuilderWrap(Package::class)
            ->setMethods(['getPackagePath'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var PackageManager|\PHPUnit_Framework_MockObject_MockObject $packageManager */
        $packageManager = $this->getMockBuilderWrap(PackageManager::class)
            ->setMethods(['isPackageActive', 'getPackage'])
            ->getMock();

        $package->method('getPackagePath')
            ->willReturn(realpath(__DIR__ . '/../../') . '/');

        $packageManager->method('isPackageActive')
            ->with($this->equalTo('formz'))
            ->will($this->returnValue(true));

        $packageManager->method('getPackage')
            ->with('formz')
            ->will($this->returnValue($package));

        /** @noinspection PhpInternalEntityUsedInspection */
        ExtensionManagementUtility::setPackageManager($packageManager);
    }

    /**
     * Stores the initial instance of the package manager.
     */
    protected function backUpPackageManager()
    {
        $reflection = new \ReflectionClass(ExtensionManagementUtility::class);
        $property = $reflection->getProperty('packageManager');
        $property->setAccessible(true);
        $this->packageManagerBackUp = $property->getValue();
    }

    /**
     * Restores the initial instance of the package manager.
     */
    protected function restorePackageManager()
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        ExtensionManagementUtility::setPackageManager($this->packageManagerBackUp);
    }

    /**
     * Will inject a mocked instance a the `ExtensionService`, allowing to
     * dynamically change the extension configuration during the tests.
     */
    protected function setUpExtensionServiceMock()
    {
        /** @var ExtensionService|\PHPUnit_Framework_MockObject_MockObject $extensionServiceMock */
        $extensionServiceMock = $this->getMockBuilderWrap(ExtensionService::class)
            ->setMethods(['getFullExtensionConfiguration', 'getExtensionRelativePath'])
            ->getMock();

        FacadeService::get()->forceInstance(ExtensionService::class, $extensionServiceMock);

        $extensionServiceMock->method('getFullExtensionConfiguration')
            ->willReturnCallback(function () {
                return $this->extensionConfiguration;
            });

        /*
         * The relative path can't be fetched during unit tests: we force a
         * static value.
         */
        $extensionServiceMock->method('getExtensionRelativePath')
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
     * Inject a mock instance of `ContextService`.
     */
    protected function setUpContextService()
    {
        /** @var ContextService|\PHPUnit_Framework_MockObject_MockObject $contextServiceMock */
        $contextServiceMock = $this->getMockBuilderWrap(ContextService::class)
            ->setMethods(['translate'])
            ->getMock();

        FacadeService::get()->forceInstance(ContextService::class, $contextServiceMock);

        $contextServiceMock->injectEnvironmentService($this->getMockedEnvironmentService());
        $contextServiceMock->injectTypoScriptService($this->getMockedTypoScriptService());

        /*
         * Mocking the translate function, to avoid the fatal error due to TYPO3
         * core trying to get the localization data in database cache.
         */
        $contextServiceMock->method('translate')
            ->will(
                $this->returnCallback(
                    function ($key, $extension) {
                        return 'LLL:' . $extension . ':' . $key;
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
     * @param array $configuration
     */
    protected function addFormzConfiguration(array $configuration)
    {
        ArrayUtility::mergeRecursiveWithOverrule(
            $this->formzConfiguration,
            [
                'config' => [
                    'tx_formz' => $configuration
                ]
            ]
        );
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
        $this->extensionConfiguration = ArrayUtility::setValueByPath($this->extensionConfiguration, $key, $value, '.');
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
            $this->mockedEnvironmentService = $this->getMockBuilderWrap(EnvironmentService::class)
                ->setMethods(['isEnvironmentInFrontendMode', 'isEnvironmentInBackendMode'])
                ->getMock();

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
     * This function will mock the `TypoScriptService` class to return a
     * custom configuration array.
     *
     * @return TypoScriptService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockedTypoScriptService()
    {
        if (null === $this->mockedTypoScriptService) {
            $this->mockedTypoScriptService = $this->getMockBuilderWrap(TypoScriptService::class)
                ->setMethods(['getFrontendTypoScriptConfiguration', 'getBackendTypoScriptConfiguration'])
                ->getMock();

            $configurationCallBack = function () {
                return $this->formzConfiguration;
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

    /**
     * Just a wrapper to have auto-completion.
     *
     * @param string $className
     * @return \PHPUnit_Framework_MockObject_MockBuilder
     */
    private function getMockBuilderWrap($className)
    {
        return $this->getMockBuilder($className);
    }
}
