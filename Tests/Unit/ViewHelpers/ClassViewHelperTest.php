<?php
namespace Romm\Formz\Tests\Unit\ViewHelpers;

use Romm\Formz\Configuration\ConfigurationFactory;
use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Core\Core;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Exceptions\InvalidEntryException;
use Romm\Formz\Exceptions\UnregisteredConfigurationException;
use Romm\Formz\Form\FormObjectFactory;
use Romm\Formz\Service\ViewHelper\FieldViewHelperService;
use Romm\Formz\Service\ViewHelper\FormViewHelperService;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\ViewHelpers\ClassViewHelper;
use TYPO3\CMS\Extbase\Error\Error;

class ClassViewHelperTest extends AbstractViewHelperUnitTest
{
    /**
     * Main function that is used to call the view helper with several scopes
     * and data, sent by the data provider.
     *
     * @test
     * @dataProvider renderViewHelperDataProvider
     *
     * @param string                 $expects   The expected result returned by the view helper.
     * @param array                  $classes   Array of classes which will be injected in the Formz configuration object.
     * @param array                  $arguments Arguments sent to the view helper.
     * @param FormViewHelperService  $formService
     * @param FieldViewHelperService $fieldService
     * @param string                 $expectedException
     */
    public function renderViewHelper(
        $expects,
        array $classes,
        array $arguments,
        FormViewHelperService $formService,
        FieldViewHelperService $fieldService,
        $expectedException = null
    ) {
        if (null !== $expectedException) {
            $this->setExpectedException($expectedException);
        }

        $classViewHelper = new ClassViewHelper;
        $classViewHelper->initializeArguments();
        $classViewHelper->injectFormService($formService);
        $classViewHelper->injectFieldService($fieldService);
        $classViewHelper->setArguments($arguments);

        $formObjectFactory = new FormObjectFactory;
        $formObjectFactory->injectConfigurationFactory(Core::instantiate(ConfigurationFactory::class));
        $formObjectFactory->injectTypoScriptService($this->getMockedTypoScriptService());
        $formObject = $formObjectFactory->getInstanceFromClassName(DefaultForm::class, 'foo');

        /** @noinspection PhpUndefinedMethodInspection */
        $formService->method('getFormObject')
            ->willReturn($formObject);

        $classesObject = $formService->getFormObject()
            ->getConfiguration()
            ->getFormzConfiguration()
            ->getView()
            ->getClasses();

        /** @noinspection PhpUndefinedMethodInspection */
        $classesObject->getErrors()
            ->setItems($classes['errors']);

        /** @noinspection PhpUndefinedMethodInspection */
        $classesObject->getValid()
            ->setItems($classes['valid']);

        $this->assertEquals($expects, $classViewHelper->render());
    }

    /**
     * Data provider for function `renderViewHelper()`.
     *
     * @return array
     */
    public function renderViewHelperDataProvider()
    {
        return [
            /*
             * Basic configuration: everything is configured correctly, but
             * no actual class is returned: only the one that is used by Formz
             * JavaScript API.
             */
            [
                'expects'      => 'formz-errors-foo',
                'classes'      => [
                    'errors' => ['foo' => 'foo'],
                    'valid'  => []
                ],
                'arguments'    => [
                    'name'  => 'errors.foo',
                    'field' => 'foo'
                ],
                'formService'  => $this->getDefaultFormService(),
                'fieldService' => $this->getDefaultFieldService()
            ],
            /*
             * Trying to call the view helper with a class namespace that does
             * not exist (something different than `errors` or `valid` for
             * instance) must throw an exception.
             */
            [
                'expects'           => null,
                'classes'           => [
                    'errors' => [],
                    'valid'  => []
                ],
                'arguments'         => [
                    'name' => 'foo.bar'
                ],
                'formService'       => $this->getDefaultFormService(),
                'fieldService'      => $this->getDefaultFieldService(),
                'expectedException' => InvalidEntryException::class
            ],
            /*
             * Trying to call the view helper with a class that was not
             * registered with TypoScript: an exception must be thrown.
             */
            [
                'expects'           => null,
                'classes'           => [
                    'errors' => [],
                    'valid'  => []
                ],
                'arguments'         => [
                    'name' => 'errors.foo'
                ],
                'formService'       => $this->getDefaultFormService(),
                'fieldService'      => $this->getDefaultFieldService(),
                'expectedException' => UnregisteredConfigurationException::class
            ],
            /*
             * If the argument `field` is not filled, and the field context is
             * not declared in the service, then no field is bound: an exception
             * must be thrown.
             */
            [
                'expects'           => null,
                'classes'           => [
                    'errors' => ['foo' => 'foo'],
                    'valid'  => []
                ],
                'arguments'         => [
                    'name' => 'errors.foo'
                ],
                'formService'       => $this->getDefaultFormService(),
                'fieldService'      => $this->getDefaultFieldService(),
                'expectedException' => EntryNotFoundException::class
            ],
            /*
             * Running this view helper with an existing field context should
             * run correctly and return a correct result.
             */
            [
                'expects'      => 'formz-errors-foo',
                'classes'      => [
                    'errors' => ['foo' => 'foo'],
                    'valid'  => []
                ],
                'arguments'    => [
                    'name' => 'errors.foo'
                ],
                'formService'  => $this->getDefaultFormService(),
                'fieldService' => $this->getFieldServiceWithField()
            ],
            /*
             * Checking that the error class is given when the form has been
             * submitted and the result does have an error for the given
             * property.
             */
            [
                'expects'      => 'formz-errors-foo foo',
                'classes'      => [
                    'errors' => ['foo' => 'foo'],
                    'valid'  => []
                ],
                'arguments'    => [
                    'name'  => 'errors.foo',
                    'field' => 'foo'
                ],
                'formService'  => $this->getServiceWithErrorResult(),
                'fieldService' => $this->getDefaultFieldService()
            ],
            /*
             * Checking that the error class is given when the form has been
             * submitted and the result has no error for the given property.
             */
            [
                'expects'      => 'formz-valid-bar bar',
                'classes'      => [
                    'errors' => [],
                    'valid'  => ['bar' => 'bar']
                ],
                'arguments'    => [
                    'name'  => 'valid.bar',
                    'field' => 'foo'
                ],
                'formService'  => $this->getServiceWithNoErrorResult(),
                'fieldService' => $this->getDefaultFieldService()
            ]
        ];
    }

    /**
     * @return FormViewHelperService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDefaultFormService()
    {
        return $this->getMockBuilder(FormViewHelperService::class)
            ->setMethods(['getFormObject'])
            ->getMock();
    }

    /**
     * @return FieldViewHelperService
     */
    protected function getDefaultFieldService()
    {
        return new FieldViewHelperService;
    }

    /**
     * @return FieldViewHelperService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFieldServiceWithField()
    {
        $field = new Field;
        $field->setFieldName('foo');

        $service = $this->getDefaultFieldService();
        $service->setCurrentField($field);

        return $service;
    }

    /**
     * @return FormViewHelperService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getServiceWithNoErrorResult()
    {
        $service = $this->getDefaultFormService();
        $service->markFormAsSubmitted();
        $service->setFormResult(new FormResult);

        return $service;
    }

    /**
     * @return FormViewHelperService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getServiceWithErrorResult()
    {
        $service = $this->getServiceWithNoErrorResult();
        $service->getFormResult()
            ->forProperty('foo')
            ->addError(new Error('foo', 1337));

        return $service;
    }
}
