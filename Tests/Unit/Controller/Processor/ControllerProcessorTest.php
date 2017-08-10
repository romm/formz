<?php
namespace Romm\Formz\Tests\Unit\Controller\Processor;

use Romm\Formz\Controller\Processor\ControllerProcessor;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Form\FormObject\FormObjectFactory;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Fixture\Form\ExtendedForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Mvc\Controller\Argument;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Request;

class ControllerProcessorTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function setDataSetsData()
    {
        $request = new Request;
        $arguments = new Arguments;
        $settings = ['foo' => 'bar'];

        $controllerProcessor = new ControllerProcessor;
        $controllerProcessor->setData($request, $arguments, $settings);

        $this->assertSame($arguments, $controllerProcessor->getRequestArguments());
        $this->assertSame($settings, $controllerProcessor->getSettings());
    }

    /**
     * To prevent infinite looping, the same request can't be dispatched more
     * than once.
     *
     * @test
     */
    public function sameRequestIsNotDispatchedMoreThanOnce()
    {
        $request = new Request;
        $request->setControllerObjectName('foo');
        $request->setControllerActionName('foo');

        $controllerProcessor = $this->getMockBuilder(ControllerProcessor::class)
            ->setMethods(['doDispatch'])
            ->getMock();

        $controllerProcessor->expects($this->once())
            ->method('doDispatch');

        $controllerProcessor->setData($request, new Arguments, []);

        $controllerProcessor->dispatch();
        $controllerProcessor->dispatch();
    }

    /**
     * @test
     */
    public function differentRequestsCanBeDispatched()
    {
        $request1 = new Request;
        $request1->setControllerObjectName('foo');
        $request1->setControllerActionName('foo');

        $request2 = new Request;
        $request2->setControllerObjectName('bar');
        $request2->setControllerActionName('bar');

        $controllerProcessor = $this->getMockBuilder(ControllerProcessor::class)
            ->setMethods(['doDispatch'])
            ->getMock();

        $controllerProcessor->expects($this->exactly(2))
            ->method('doDispatch');

        $controllerProcessor->setData($request1, new Arguments, []);

        $controllerProcessor->dispatch();
        $controllerProcessor->dispatch();
        $controllerProcessor->dispatch();

        $controllerProcessor->setData($request2, new Arguments, []);

        $controllerProcessor->dispatch();
        $controllerProcessor->dispatch();
        $controllerProcessor->dispatch();
    }

    /**
     * When the request has been dispatched to internal controller (after the
     * "stop action" exception has been thrown), the original request object
     * must be accessible.
     *
     * @test
     */
    public function originalRequestIsCloned()
    {
        $request = new Request;
        $request->setControllerObjectName('foo');
        $request->setControllerActionName('foo');
        $requestSerialized = serialize($request);

        $arguments = new Arguments;
        $arguments->addArgument(new Argument('form', DefaultForm::class));

        $controllerProcessor = new ControllerProcessor;
        $controllerProcessor->setData($request, $arguments, []);

        $formObjectFactoryMock = $this->getFormObjectFactoryMock();
        $controllerProcessor->injectFormObjectFactory($formObjectFactoryMock);
        $formObjectFactoryMock->expects($this->once())
            ->method('getInstanceWithClassName')
            ->willReturn($this->getFormObject());

        try {
            $exception = null;
            $controllerProcessor->dispatch();
        } catch (StopActionException $exception) {
        }

        // A "stop action" exception must have been thrown.
        $this->assertNotNull($exception);

        /*
         * The request must be cloned: the instance must not be the same as the
         * one that was passed as an argument, but it must contain the same
         * data.
         */
        $this->assertNotSame($request, $controllerProcessor->getRequest());
        $this->assertSame($requestSerialized, serialize($controllerProcessor->getRequest()));
    }

    /**
     *
     *
     * @test
     */
    public function getRequestFormsReturnsOnlyFormArguments()
    {
        $arguments = new Arguments;
        $arguments->addArgument(new Argument('form1', DefaultForm::class));
        $arguments->addArgument(new Argument('foo', 'string'));
        $arguments->addArgument(new Argument('form2', ExtendedForm::class));
        $arguments->addArgument(new Argument('bar', 'int'));

        $controllerProcessor = new ControllerProcessor;
        $controllerProcessor->setData(new Request, $arguments, []);

        $formObject1 = $this->prophesize(FormObject::class)->reveal();
        $formObject2 = $this->prophesize(FormObject::class)->reveal();

        $formObjectFactoryMock = $this->getFormObjectFactoryMock();
        $controllerProcessor->injectFormObjectFactory($formObjectFactoryMock);

        $formObjectFactoryMock->expects($this->at(0))
            ->method('getInstanceWithClassName')
            ->willReturn($formObject1);
        $formObjectFactoryMock->expects($this->at(1))
            ->method('getInstanceWithClassName')
            ->willReturn($formObject2);

        $requestForms = $controllerProcessor->getRequestForms();

        $this->assertSame(
            [
                'form1' => $formObject1,
                'form2' => $formObject2
            ],
            $requestForms
        );
    }

    /**
     * Checks that the request is correctly dispatched to the internal
     * controller.
     *
     * @test
     */
    public function requestIsDispatchedToInternalController()
    {
        $arguments = new Arguments;
        $arguments->addArgument(new Argument('form', DefaultForm::class));

        $request = new Request;
        $request->setControllerName('foo');
        $request->setControllerActionName('bar');

        $controllerProcessor = new ControllerProcessor;
        $controllerProcessor->setData($request, $arguments, []);

        $formObjectFactoryMock = $this->getFormObjectFactoryMock();
        $controllerProcessor->injectFormObjectFactory($formObjectFactoryMock);
        $formObjectFactoryMock->expects($this->once())
            ->method('getInstanceWithClassName')
            ->willReturn($this->getFormObject());

        try {
            $exception = null;
            $controllerProcessor->dispatch();
        } catch (StopActionException $exception) {
            // Checking that the request goes to the correct controller action.
            $this->assertEquals('Romm', $request->getControllerVendorName());
            $this->assertEquals('Formz', $request->getControllerExtensionName());
            $this->assertEquals('Form', $request->getControllerName());
            $this->assertEquals('processForm', $request->getControllerActionName());
        }

        // A "stop action" exception must have been thrown.
        $this->assertNotNull($exception);
    }

    /**
     * If at least one error is found in the definitions of the form objects,
     * the request must be forwarded to another action that will handle the
     * errors.
     *
     * @test
     */
    public function formObjectWithDefinitionErrorForwardsToErrorAction()
    {
        $exception = null;
        $request = new Request;
        $arguments = new Arguments;
        $arguments->addArgument(new Argument('form', DefaultForm::class));

        $controllerProcessor = new ControllerProcessor;
        $controllerProcessor->setData($request, $arguments, []);

        $formObject = $this->getFormObject(true);

        $formObjectFactoryMock = $this->getFormObjectFactoryMock();
        $controllerProcessor->injectFormObjectFactory($formObjectFactoryMock);
        $formObjectFactoryMock->expects($this->once())
            ->method('getInstanceWithClassName')
            ->willReturn($formObject);

        try {
            $controllerProcessor->dispatch();
        } catch (StopActionException $exception) {
            // Checking that the request goes to the correct controller action.
            $this->assertEquals('Romm', $request->getControllerVendorName());
            $this->assertEquals('Formz', $request->getControllerExtensionName());
            $this->assertEquals('Form', $request->getControllerName());
            $this->assertEquals('formObjectError', $request->getControllerActionName());

            // An argument containing the form object with the errors must exist.
            $this->assertSame(
                ['formObject' => $formObject],
                $request->getArguments()
            );
        }

        // A "stop action" exception must have been thrown.
        $this->assertNotNull($exception);
    }

    /**
     * In case the
     *
     * @test
     */
    public function requestIsNotDispatchedIfNoFormArgumentIsFound()
    {
        $controllerProcessor = new ControllerProcessor;
        $controllerProcessor->setData(new Request, new Arguments, []);
        $controllerProcessor->injectFormObjectFactory($this->getFormObjectFactoryMock());

        // No "stop action" exception should be thrown.
        $controllerProcessor->dispatch();
    }

    /**
     * @return FormObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFormObjectFactoryMock()
    {
        return $this->getMockBuilder(FormObjectFactory::class)
            ->setMethods(['getInstanceWithClassName'])
            ->getMock();
    }

    /**
     * @param bool $definitionError
     * @return FormObject
     */
    protected function getFormObject($definitionError = false)
    {
        $formObject = $this->prophesize(FormObject::class);

        $resultWithError = new Result;

        if ($definitionError) {
            $resultWithError->addError(new Error('foo', 42));
        }

        $formObject->getDefinitionValidationResult()->willReturn($resultWithError);

        return $formObject->reveal();
    }
}
