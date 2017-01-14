<?php
namespace Romm\Formz\Tests\Unit\ViewHelpers;

use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Exceptions\InvalidEntryException;
use Romm\Formz\Exceptions\UnregisteredConfigurationException;
use Romm\Formz\Form\FormObjectFactory;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\ViewHelpers\ClassViewHelper;
use Romm\Formz\ViewHelpers\Service\FormzViewHelperService;
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
     * @param FormzViewHelperService $service
     * @param string                 $expectedException
     */
    public function renderViewHelper(
        $expects,
        array $classes,
        array $arguments,
        FormzViewHelperService $service,
        $expectedException = null
    ) {
        if (null !== $expectedException) {
            $this->setExpectedException($expectedException);
        }

        $classViewHelper = new ClassViewHelper;
        $classViewHelper->initializeArguments();
        $classViewHelper->injectFormzViewHelperService($service);
        $classViewHelper->setArguments($arguments);

        $formObjectFactory = new FormObjectFactory;
        $formObject = $formObjectFactory->getInstanceFromClassName(DefaultForm::class, 'foo');

        /** @noinspection PhpUndefinedMethodInspection */
        $service->method('getFormObject')
            ->willReturn($formObject);

        $classesObject = $service->getFormObject()
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
                'expects'   => 'formz-errors-foo',
                'classes'   => [
                    'errors' => ['foo' => 'foo'],
                    'valid'  => []
                ],
                'arguments' => [
                    'name'  => 'errors.foo',
                    'field' => 'foo'
                ],
                'service'   => $this->getDefaultService()
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
                'service'           => $this->getDefaultService(),
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
                'service'           => $this->getDefaultService(),
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
                'service'           => $this->getDefaultService(),
                'expectedException' => EntryNotFoundException::class
            ],
            /*
             * Running this view helper with an existing field context should
             * run correctly and return a correct result.
             */
            [
                'expects'   => 'formz-errors-foo',
                'classes'   => [
                    'errors' => ['foo' => 'foo'],
                    'valid'  => []
                ],
                'arguments' => [
                    'name' => 'errors.foo'
                ],
                'service'   => $this->getServiceWithField()
            ],
            /*
             * Checking that the error class is given when the form has been
             * submitted and the result does have an error for the given
             * property.
             */
            [
                'expects'   => 'formz-errors-foo foo',
                'classes'   => [
                    'errors' => ['foo' => 'foo'],
                    'valid'  => []
                ],
                'arguments' => [
                    'name'  => 'errors.foo',
                    'field' => 'foo'
                ],
                'service'   => $this->getServiceWithErrorResult()
            ],
            /*
             * Checking that the error class is given when the form has been
             * submitted and the result has no error for the given property.
             */
            [
                'expects'   => 'formz-valid-bar bar',
                'classes'   => [
                    'errors' => [],
                    'valid'  => ['bar' => 'bar']
                ],
                'arguments' => [
                    'name'  => 'valid.bar',
                    'field' => 'foo'
                ],
                'service'   => $this->getServiceWithNoErrorResult()
            ]
        ];
    }

    /**
     * @return FormzViewHelperService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDefaultService()
    {
        return $this->getMock(FormzViewHelperService::class, ['getFormObject']);
    }

    /**
     * @return FormzViewHelperService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getServiceWithField()
    {
        $field = new Field;
        $field->setFieldName('foo');

        $service = $this->getDefaultService();
        $service->setCurrentField($field);

        return $service;
    }

    /**
     * @return FormzViewHelperService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getServiceWithNoErrorResult()
    {
        $service = $this->getDefaultService();
        $service->markFormAsSubmitted();
        $service->setFormResult(new FormResult);

        return $service;
    }

    /**
     * @return FormzViewHelperService|\PHPUnit_Framework_MockObject_MockObject
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
