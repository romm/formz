<?php
namespace Romm\Formz\Tests\Unit\ViewHelpers;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Romm\Formz\Core\Core;
use Romm\Formz\Exceptions\ContextNotFoundException;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Exceptions\InvalidArgumentTypeException;
use Romm\Formz\Exceptions\InvalidArgumentValueException;
use Romm\Formz\Exceptions\PropertyNotAccessibleException;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Form\FormObject\FormObjectFactory;
use Romm\Formz\Service\ViewHelper\FieldViewHelperService;
use Romm\Formz\Service\ViewHelper\FormViewHelperService;
use Romm\Formz\Service\ViewHelper\SlotViewHelperService;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\ViewHelpers\FieldViewHelper;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\CacheFactory;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\Core\ViewHelper\TemplateVariableContainer;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3\CMS\Fluid\View\StandaloneView;

class FieldViewHelperTest extends AbstractViewHelperUnitTest
{
    /**
     * @param string     $fieldName
     * @param string     $layoutName
     * @param FormObject $formObject
     * @param string     $expectedException
     * @dataProvider renderViewHelperDataProvider
     * @test
     */
    public function renderViewHelper($fieldName, $layoutName, FormObject $formObject, $expectedException = null)
    {
        if (null !== $expectedException) {
            $this->setExpectedException($expectedException);
        }

        $viewHelper = $this->getMockedFieldViewHelper();

        $formServiceMock = $this->getMockedFormService($formObject);
        $viewHelper->injectFormService($formServiceMock);
        $formServiceMock->expects($this->once())
            ->method('formContextExists')
            ->willReturn(true);

        $fieldServiceMock = $this->getMockedFieldService();
        $viewHelper->injectFieldService($fieldServiceMock);
        $fieldServiceMock->expects($this->once())
            ->method('setCurrentField')
            ->with($formObject->getDefinition()->getField($fieldName));

        $viewHelper->injectSlotService(new SlotViewHelperService);
        $viewHelper->setRenderingContext($this->getMockedRenderingContext());

        $viewHelper->setArguments([
            'name'   => $fieldName,
            'layout' => $layoutName
        ]);
        $viewHelper->initializeArguments();

        $this->setUpPackageManagerMock();
        $viewHelper->render();
    }

    /**
     * @return array
     */
    public function renderViewHelperDataProvider()
    {
        $this->injectAllDependencies();
        $this->registerFluidTemplateCache();
        $this->addFooLayoutToTypoScriptConfiguration();

        /** @var FormObjectFactory $formObjectFactory */
        $formObjectFactory = Core::instantiate(FormObjectFactory::class);
        $formObject = $formObjectFactory->getInstanceWithClassName(DefaultForm::class, 'foo');

        return [
            [
                'fieldName'  => 'foo',
                'layoutName' => 'foo',
                'formObject' => $formObject
            ]
        ];
    }

    /**
     * The Field view helper must always be called from within a form view
     * helper. Otherwise, an exception must be thrown.
     *
     * @test
     */
    public function renderFieldOutsideFormThrowsException()
    {
        $this->setExpectedException(ContextNotFoundException::class);

        $viewHelper = new FieldViewHelper;
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->injectFormService(new FormViewHelperService);
        $viewHelper->render();
    }

    /**
     * The argument `name` of the Field view helper should be a string.
     * Otherwise, an exception must be thrown.
     *
     * @test
     */
    public function renderFieldWithInvalidFieldNameTypeThrowsException()
    {
        $this->setExpectedException(InvalidArgumentTypeException::class);

        $viewHelper = new FieldViewHelper;
        $this->injectDependenciesIntoViewHelper($viewHelper);

        $formService = new FormViewHelperService;
        $formService->setFormObject($this->getDefaultFormObject());
        $formService->activateFormContext();
        $viewHelper->injectFormService($formService);

        $viewHelper->setArguments(['name' => true]);
        $viewHelper->initializeArguments();

        $viewHelper->render();
    }

    /**
     * Trying to render a field that does not exist in the form configuration
     * must throw an exception.
     *
     * @test
     */
    public function renderNotExistingFieldThrowsException()
    {
        $this->setExpectedException(PropertyNotAccessibleException::class);

        /** @var FormObjectFactory $formObjectFactory */
        $formObjectFactory = Core::instantiate(FormObjectFactory::class);
        $formObject = $formObjectFactory->getInstanceWithClassName(DefaultForm::class, 'foo');

        /** @var FieldViewHelper|\PHPUnit_Framework_MockObject_MockObject $viewHelper */
        $viewHelper = $this->getMockBuilder(FieldViewHelper::class)
            ->setMethods(['renderChildren'])
            ->getMock();
        $viewHelper->injectFormService($this->getMockedFormService($formObject));
        $viewHelper->injectFieldService($this->getMockedFieldService());
        $viewHelper->setRenderingContext($this->getMockedRenderingContext());

        $viewHelper->setArguments(['name' => 'bar']);
        $viewHelper->initializeArguments();

        $viewHelper->render();
    }

    /**
     * The argument `layout` of the Field view helper should be a string.
     * Otherwise, an exception must be thrown.
     *
     * @test
     */
    public function renderFieldWithInvalidLayoutNameTypeThrowsException()
    {
        $this->setExpectedException(InvalidArgumentTypeException::class);

        /** @var FormObjectFactory $formObjectFactory */
        $formObjectFactory = Core::instantiate(FormObjectFactory::class);
        $formObject = $formObjectFactory->getInstanceWithClassName(DefaultForm::class, 'foo');

        /** @var FieldViewHelper|\PHPUnit_Framework_MockObject_MockObject $viewHelper */
        $viewHelper = $this->getMockBuilder(FieldViewHelper::class)
            ->setMethods(['renderChildren'])
            ->getMock();
        $viewHelper->injectFormService($this->getMockedFormService($formObject));
        $viewHelper->injectFieldService($this->getMockedFieldService());
        $viewHelper->injectSlotService(new SlotViewHelperService);
        $viewHelper->setRenderingContext($this->getMockedRenderingContext());

        $viewHelper->setArguments([
            'name'   => 'foo',
            'layout' => true // Should not be a boolean.
        ]);
        $viewHelper->initializeArguments();

        $viewHelper->render();
    }

    /**
     * The argument `layout` of the Field view helper must not be empty.
     *
     * @test
     */
    public function renderFieldWithEmptyLayoutNameThrowsException()
    {
        $this->setExpectedException(InvalidArgumentValueException::class);

        /** @var FormObjectFactory $formObjectFactory */
        $formObjectFactory = Core::instantiate(FormObjectFactory::class);
        $formObject = $formObjectFactory->getInstanceWithClassName(DefaultForm::class, 'foo');

        /** @var FieldViewHelper|\PHPUnit_Framework_MockObject_MockObject $viewHelper */
        $viewHelper = $this->getMockBuilder(FieldViewHelper::class)
            ->setMethods(['renderChildren'])
            ->getMock();
        $viewHelper->injectFormService($this->getMockedFormService($formObject));
        $viewHelper->injectFieldService($this->getMockedFieldService());
        $viewHelper->injectSlotService(new SlotViewHelperService);
        $viewHelper->setRenderingContext($this->getMockedRenderingContext());

        $viewHelper->setArguments([
            'name'   => 'foo',
            'layout' => '' // Should not be empty.
        ]);
        $viewHelper->initializeArguments();

        $viewHelper->render();
    }

    /**
     * The argument `layout` of the Field view helper must contain a know layout
     * name, configured in TypoScript.
     *
     * @test
     */
    public function renderFieldWithNotExistingLayoutNameThrowsException()
    {
        $this->setExpectedException(EntryNotFoundException::class, '', 1465243586);
        $this->addFooLayoutToTypoScriptConfiguration();

        /** @var FormObjectFactory $formObjectFactory */
        $formObjectFactory = Core::instantiate(FormObjectFactory::class);
        $formObject = $formObjectFactory->getInstanceWithClassName(DefaultForm::class, 'foo');

        /** @var FieldViewHelper|\PHPUnit_Framework_MockObject_MockObject $viewHelper */
        $viewHelper = $this->getMockBuilder(FieldViewHelper::class)
            ->setMethods(['renderChildren'])
            ->getMock();
        $viewHelper->injectFormService($this->getMockedFormService($formObject));
        $viewHelper->injectFieldService($this->getMockedFieldService());
        $viewHelper->injectSlotService(new SlotViewHelperService);
        $viewHelper->setRenderingContext($this->getMockedRenderingContext());

        $viewHelper->setArguments([
            'name'   => 'foo',
            'layout' => 'bar' // Does not exist in configuration.
        ]);
        $viewHelper->initializeArguments();

        $viewHelper->render();
    }

    /**
     * The second part of the layout name should be an existing item in the
     * layout configuration.
     *
     * @test
     */
    public function renderFieldWithNotExistingLayoutItemNameThrowsException()
    {
        $this->setExpectedException(EntryNotFoundException::class, '', 1485867803);
        $this->addFooLayoutToTypoScriptConfiguration();

        /** @var FormObjectFactory $formObjectFactory */
        $formObjectFactory = Core::instantiate(FormObjectFactory::class);
        $formObject = $formObjectFactory->getInstanceWithClassName(DefaultForm::class, 'foo');

        /** @var FieldViewHelper|\PHPUnit_Framework_MockObject_MockObject $viewHelper */
        $viewHelper = $this->getMockBuilder(FieldViewHelper::class)
            ->setMethods(['renderChildren'])
            ->getMock();
        $viewHelper->injectFormService($this->getMockedFormService($formObject));
        $viewHelper->injectFieldService($this->getMockedFieldService());
        $viewHelper->injectSlotService(new SlotViewHelperService);
        $viewHelper->setRenderingContext($this->getMockedRenderingContext());

        $viewHelper->setArguments([
            'name'   => 'foo',
            'layout' => 'foo.bar' // `bar` does not exist in configuration.
        ]);
        $viewHelper->initializeArguments();

        $viewHelper->render();
    }

    /**
     * The Field view helper needs to put some variables in the template
     * variable container. However, if these variables were already defined
     * before the view helper is called, they must be restored after the view
     * helper has been rendered.
     *
     * In this test, we fill these variables with `bar`, and force the view
     * helper to fill them with `foo` during the rendering. We test after the
     * rendering that they are reset to their previous values.
     *
     * @test
     */
    public function originalArgumentsAreRestoredAfterViewHelperIsRendered()
    {
        /** @var FormObjectFactory $formObjectFactory */
        $formObjectFactory = Core::instantiate(FormObjectFactory::class);
        $formObject = $formObjectFactory->getInstanceWithClassName(DefaultForm::class, 'foo');

        /** @var FieldViewHelper|\PHPUnit_Framework_MockObject_MockObject $viewHelper */
        $viewHelper = $this->getMockBuilder(FieldViewHelper::class)
            ->setMethods(['renderChildren'])
            ->getMock();
        $viewHelper->injectFormService($this->getMockedFormService($formObject));
        $viewHelper->injectFieldService($this->getMockedFieldService());
        $viewHelper->injectSlotService(new SlotViewHelperService);

        $renderingContextMock = $this->getMockedRenderingContext();

        /** @var TemplateVariableContainer|ObjectProphecy $templateVariableContainerProphecy */
        $templateVariableContainerProphecy = $this->prophesize(TemplateVariableContainer::class);

        /** @var TemplateVariableContainer $templateVariableContainer */
        $templateVariableContainer = $templateVariableContainerProphecy->reveal();

        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '8.0.0', '<')) {
            $renderingContextMock->injectTemplateVariableContainer($templateVariableContainer);
        } else {
            $renderingContextMock->setVariableProvider($templateVariableContainer);
        }

        $viewHelper->setRenderingContext($renderingContextMock);

        $arguments = [];
        $templateVariables = [];

        $templateVariableContainerProphecy
            ->add(Argument::type('string'), Argument::any())
            ->shouldBeCalled()
            ->will(function ($arguments) use ($templateVariableContainerProphecy, &$templateVariables) {
                $templateVariables[$arguments[0]] = $arguments[1];

                if (in_array($arguments[0], FieldViewHelper::$reservedVariablesNames)) {
                    $templateVariableContainerProphecy
                        ->remove($arguments[0])
                        ->shouldBeCalled()
                        ->will(function () use ($arguments, &$templateVariables) {
                            unset($templateVariables[$arguments[0]]);
                        });
                }

                $templateVariableContainerProphecy
                    ->exists($arguments[0])
                    ->willReturn(true);

                $templateVariableContainerProphecy
                    ->get($arguments[0])
                    ->willReturn($arguments[1]);
            });

        $templateVariableContainerProphecy
            ->getAllIdentifiers()
            ->will(function () use (&$templateVariables) {
                return array_keys($templateVariables);
            });

        $templateVariableContainerProphecy
            ->getAll()
            ->will(function () use (&$templateVariables) {
                return $templateVariables;
            });

        $templateVariableContainer->add('foo', 'bar');
        foreach (FieldViewHelper::$reservedVariablesNames as $key) {
            $arguments[$key] = 'foo';
            $templateVariableContainer->add($key, 'bar');
        }

        $viewHelper->setArguments([
            'name'      => 'foo',
            'layout'    => 'foo',
            'arguments' => $arguments
        ]);
        $viewHelper->initializeArguments();

        $this->setUpPackageManagerMock();
        $viewHelper->render();

        $this->assertEquals(
            [
                'foo'       => 'bar',
                'layout'    => 'bar',
                'formName'  => 'bar',
                'fieldName' => 'bar',
                'fieldId'   => 'bar',
            ],
            $templateVariableContainer->getAll()
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FieldViewHelper
     */
    protected function getMockedFieldViewHelper()
    {
        /** @var FieldViewHelper|\PHPUnit_Framework_MockObject_MockObject $viewHelper */
        $viewHelper = $this->getMockBuilder(FieldViewHelper::class)
            ->setMethods(['renderChildren'])
            ->getMock();
        $this->injectDependenciesIntoViewHelper($viewHelper);

        return $viewHelper;
    }

    /**
     * @param FormObject $formObject
     * @return FormViewHelperService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockedFormService(FormObject $formObject)
    {
        /** @var FormViewHelperService|\PHPUnit_Framework_MockObject_MockObject $formService */
        $formService = $this->getMockBuilder(FormViewHelperService::class)
            ->setMethods(['formContextExists'])
            ->getMock();
        $formService->setFormObject($formObject);

        return $formService;
    }

    /**
     * @return FieldViewHelperService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockedFieldService()
    {
        $fieldService = $this->getMockBuilder(FieldViewHelperService::class)
            ->setMethods(['setCurrentField'])
            ->getMock();

        return $fieldService;
    }

    /**
     * @return RenderingContext|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockedRenderingContext()
    {
        $mockRequest = $this->getMockBuilder(Request::class)
            ->getMock();

        /** @var ControllerContext|\PHPUnit_Framework_MockObject_MockObject $controllerContextMock */
        $controllerContextMock = $this->getMockBuilder(ControllerContext::class)
            ->setMethods(['getRequest'])
            ->getMock();
        $controllerContextMock->method('getRequest')
            ->willReturn($mockRequest);

        $view = new StandaloneView;
        $view->setControllerContext($controllerContextMock);

        $viewHelperVariableContainer = new ViewHelperVariableContainer;
        $viewHelperVariableContainer->setView($view);

        /** @var TemplateVariableContainer|\PHPUnit_Framework_MockObject_MockObject $templateVariableContainer */
        $templateVariableContainer = $this->getMockBuilder(TemplateVariableContainer::class)
            ->setMethods(['add', 'exists', 'remove'])
            ->getMock();

        $renderingContext = new RenderingContext;
        $this->inject($renderingContext, 'viewHelperVariableContainer', $viewHelperVariableContainer);

        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '8.0.0', '<')) {
            $renderingContext->injectTemplateVariableContainer($templateVariableContainer);
        } else {
            $renderingContext->setVariableProvider($templateVariableContainer);
        }

        $renderingContext->setControllerContext($controllerContextMock);

        return $renderingContext;
    }

    /**
     * We need to manually register the cache `fluid_template` for TYPO3 <= 6.2
     * tests.
     */
    protected function registerFluidTemplateCache()
    {
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '7.6.0', '<')) {
            /** @var CacheManager $cacheManager */
            $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
            $cacheFactory = new CacheFactory('foo', $cacheManager);
            $cacheInstance = $cacheFactory->create('fluid_template', PhpFrontend::class, NullBackend::class);

            if (false === $cacheManager->hasCache('fluid_template')) {
                $cacheManager->registerCache($cacheInstance);
            }
        }
    }

    /**
     * Adds a default configuration for layouts, which can be used in the tests.
     */
    protected function addFooLayoutToTypoScriptConfiguration()
    {
        $this->addFormzConfiguration([
            'view' => [
                'layouts' => [
                    'foo' => [
                        'templateFile' => 'EXT:formz/Tests/Fixture/ViewHelpers/StandaloneViewFixture.html',
                        'items'        => [
                            'default' => [
                                'layout' => 'foo/bar'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }
}
