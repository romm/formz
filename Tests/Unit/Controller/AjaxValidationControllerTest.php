<?php
namespace Romm\Formz\Tests\Unit\Controller;

use Prophecy\Prophecy\ObjectProphecy;
use ReflectionObject;
use Romm\Formz\Controller\AjaxValidationController;
use Romm\Formz\Core\Core;
use Romm\Formz\Exceptions\ClassNotFoundException;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Exceptions\InvalidArgumentTypeException;
use Romm\Formz\Exceptions\InvalidConfigurationException;
use Romm\Formz\Exceptions\MissingArgumentException;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Service\ContextService;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Fixture\Validation\Validator\DummyValidator;
use Romm\Formz\Tests\Fixture\Validation\Validator\ExceptionDummyValidator;
use Romm\Formz\Tests\Fixture\Validation\Validator\MessagesValidator;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Validation\Validator\RequiredValidator;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Mvc\View\JsonView;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Response;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;

class AjaxValidationControllerTest extends AbstractUnitTest
{
    /**
     * Trying to call the controller with a different method than `POST` must
     * throw a wrong status to the request.
     *
     * @test
     */
    public function callingMethodWithUnexpectedMethodThrowsRequestStatus()
    {
        $ajaxValidationController = $this->getAjaxValidationControllerMock();

        $ajaxValidationController->expects($this->once())
            ->method('throwStatus');

        /** @var Request|ObjectProphecy $requestProphecy */
        $requestProphecy = $this->prophesize(Request::class);

        $requestProphecy->getMethod()
            ->shouldBeCalled()
            ->willReturn('GET');

        $this->inject($ajaxValidationController, 'request', $requestProphecy->reveal());

        $ajaxValidationController->initializeAction();
    }

    /**
     * Trying to call the controller with a `POST` method should not throw a
     * wrong status to the request.
     *
     * @test
     */
    public function callingMethodWithPostMethodDoesNotThrowRequestStatus()
    {
        $ajaxValidationController = $this->getAjaxValidationControllerMock();

        $ajaxValidationController->expects($this->never())
            ->method('throwStatus');

        /** @var Request|ObjectProphecy $requestProphecy */
        $requestProphecy = $this->prophesize(Request::class);

        $requestProphecy->getMethod()
            ->shouldBeCalled()
            ->willReturn('POST');

        $this->inject($ajaxValidationController, 'request', $requestProphecy->reveal());

        $ajaxValidationController->initializeAction();
    }

    /**
     * When calling the request in protected mode, all exceptions should be
     * catch by the controller.
     *
     * @test
     */
    public function protectedRequestModeMustCatchExceptions()
    {
        $ajaxValidationController = $this->getAjaxValidationControllerMock();

        $ajaxValidationController->expects($this->once())
            ->method('setUpResponseResult')
            ->willReturnCallback(function ($result) {
                $this->assertFalse($result['success']);
                $this->assertEquals(
                    ContextService::get()->translate(AjaxValidationController::DEFAULT_ERROR_MESSAGE_KEY),
                    $result['messages']['errors']['unknown-1']
                );
            });

        $ajaxValidationController->processRequest(new Request, new Response);

        $ajaxValidationController->expects($this->never())
            ->method('getDebugMessageForException');
    }

    /**
     * If an exception is catch during the request, and if FormZ debug mode is
     * activated, the result message must be customized to contain information
     * about the exception.
     *
     * @test
     */
    public function exceptionCatchInProtectedRequestWithDebugModeShouldCustomizeResultMessage()
    {
        $formObject = $this->getDefaultFormObject();
        $validator = $formObject->getDefinition()->getField('foo')->addValidator('bar', ExceptionDummyValidator::class);
        $validator->activateAjaxUsage();

        $ajaxValidationController = $this->getAjaxValidationControllerMock($formObject);

        $ajaxValidationController->expects($this->once())
            ->method('getDebugMessageForException');

        $this->setExtensionConfigurationValue('debugMode', true);

        $ajaxValidationController->expects($this->once())
            ->method('setUpResponseResult')
            ->willReturnCallback(function ($result) {
                $this->assertFalse($result['success']);
            });

        $ajaxValidationController->processRequest(new Request, new Response);
    }

    /**
     * @test
     * @dataProvider requestArgumentMissingThrowsExceptionDataProvider
     * @param RequestInterface $request
     * @param string           $exceptionType
     * @param string           $exceptionCode
     */
    public function requestArgumentMissingThrowsException(RequestInterface $request, $exceptionType = null, $exceptionCode = null)
    {
        if ($exceptionType) {
            $this->setExpectedException($exceptionType, '', $exceptionCode);
        }

        /** @var AjaxValidationController|\PHPUnit_Framework_MockObject_MockObject $controller */
        $controller = $this->getMockBuilder(AjaxValidationController::class)
            ->setMethods(['getRequest', 'resolveActionMethodName', 'initializeActionMethodArguments', 'initializeActionMethodValidatorsParent'])
            ->getMock();

        $controller->method('getRequest')
            ->willReturn($request);

        $controller->method('resolveActionMethodName')
            ->willReturn('runAction');

        $this->inject($controller, 'reflectionService', new ReflectionService);
        $this->inject($controller, 'objectManager', Core::get()->getObjectManager());

        $controller->setProtectedRequestMode(false);
        $controller->processRequest(new Request, new Response);
    }

    /**
     * @return array
     */
    public function requestArgumentMissingThrowsExceptionDataProvider()
    {
        /** @var Request|ObjectProphecy $request1 */
        $request1 = $this->prophesize(Request::class);
        $request1->hasArgument('name')
            ->shouldBeCalled()
            ->willReturn(false);

        /** @var Request|ObjectProphecy $request1 */
        $request2 = $this->prophesize(Request::class);
        $request2->hasArgument('name')
            ->shouldBeCalled()
            ->willReturn(true);
        $request2->hasArgument('className')
            ->shouldBeCalled()
            ->willReturn(false);

        /** @var Request|ObjectProphecy $request1 */
        $request3 = $this->prophesize(Request::class);
        $request3->hasArgument('name')
            ->shouldBeCalled()
            ->willReturn(true);
        $request3->hasArgument('className')
            ->shouldBeCalled()
            ->willReturn(true);
        $request3->getArgument('className')
            ->shouldBeCalled()
            ->willReturn('undefined class');

        /** @var Request|ObjectProphecy $request1 */
        $request4 = $this->prophesize(Request::class);
        $request4->hasArgument('name')
            ->shouldBeCalled()
            ->willReturn(true);
        $request4->hasArgument('className')
            ->shouldBeCalled()
            ->willReturn(true);
        $request4->getArgument('className')
            ->shouldBeCalled()
            ->willReturn(\stdClass::class);

        return [
            [
                'request'       => $request1->reveal(),
                'exceptionType' => MissingArgumentException::class,
                'exceptionCode' => 1490179179
            ],
            [
                'request'       => $request2->reveal(),
                'exceptionType' => MissingArgumentException::class,
                'exceptionCode' => 1490179250
            ],
            [
                'request'       => $request3->reveal(),
                'exceptionType' => ClassNotFoundException::class,
                'exceptionCode' => 1490179346
            ],
            [
                'request'       => $request4->reveal(),
                'exceptionType' => InvalidArgumentTypeException::class,
                'exceptionCode' => 1490179427
            ]
        ];
    }

    /**
     * If the form configuration contains error(s), an exception must be thrown.
     *
     * @test
     */
    public function incorrectFormConfigurationThrowsException()
    {
        $this->setExpectedException(InvalidConfigurationException::class, '', 1487671395);

        $formObject = $this->getDefaultFormObject();
        $formObject->getDefinitionValidationResult()->addError(new Error('foo', 42));

        $ajaxValidationController = $this->getAjaxValidationControllerMock($formObject);

        $ajaxValidationController->setProtectedRequestMode(false);
        $ajaxValidationController->runAction('foo', DefaultForm::class, 'foo', 'bar');
    }

    /**
     * If the field name sent in the arguments is not found in the form
     * configuration, an exception must be thrown.
     *
     * @test
     */
    public function validatingUnknownFieldThrowsException()
    {
        $this->setExpectedException(EntryNotFoundException::class, '', 1487671603);

        $ajaxValidationController = $this->getAjaxValidationControllerMock();

        $ajaxValidationController->setProtectedRequestMode(false);
        $ajaxValidationController->runAction('foo', DefaultForm::class, 'unknown', 'bar');
    }

    /**
     * If the validation name sent in the arguments is not found in the field
     * configuration, an exception must be thrown.
     *
     * @test
     */
    public function validatingFieldWithUnknownValidationThrowsException()
    {
        $this->setExpectedException(EntryNotFoundException::class, '', 1487672956);
        $ajaxValidationController = $this->getAjaxValidationControllerMock();

        $ajaxValidationController->setProtectedRequestMode(false);
        $ajaxValidationController->runAction('foo', DefaultForm::class, 'foo', 'bar');
    }

    /**
     * If the validation for the field is not marked as activated for Ajax
     * calls, an exception must be thrown.
     *
     * @test
     */
    public function validationFieldValidationWithAjaxDeactivatedThrowsException()
    {
        $this->setExpectedException(InvalidConfigurationException::class, '', 1487673434);

        $formObject = $this->getDefaultFormObject();
        $formObject->getDefinition()->getField('foo')->addValidator('bar', RequiredValidator::class);

        $ajaxValidationController = $this->getAjaxValidationControllerMock($formObject);

        $ajaxValidationController->setProtectedRequestMode(false);
        $ajaxValidationController->runAction('foo', DefaultForm::class, 'foo', 'bar');
    }

    /**
     * @test
     */
    public function validationWithValidValueWorks()
    {
        $expectedResult = [
            'success'  => true,
            'messages' => [
                'errors'   => [],
                'warnings' => [],
                'notices'  => []
            ],
            'data'     => []
        ];

        $formObject = $this->getDefaultFormObject();
        $validator = $formObject->getDefinition()->getField('foo')->addValidator('bar', DummyValidator::class);
        $validator->activateAjaxUsage();

        $ajaxValidationController = $this->getAjaxValidationControllerMock($formObject);

        $ajaxValidationController->setProtectedRequestMode(false);

        $ajaxValidationController->expects($this->once())
            ->method('setUpResponseResult')
            ->willReturnCallback(function ($result) use ($expectedResult) {
                $this->assertEquals($expectedResult, $result);
            });

        $ajaxValidationController->processRequest(new Request, new Response);
    }

    /**
     * Checks that errors, warnings and notices are added to the result array.
     *
     * @test
     */
    public function messagesTypesAreReturnedInResult()
    {
        $expectedResult = [
            'success'  => false,
            'messages' => [
                'errors'   => [MessagesValidator::MESSAGE_1 => MessagesValidator::MESSAGE_1],
                'warnings' => [MessagesValidator::MESSAGE_2 => MessagesValidator::MESSAGE_2],
                'notices'  => [MessagesValidator::MESSAGE_3 => MessagesValidator::MESSAGE_3]
            ],
            'data'     => []
        ];

        $formObject = $this->getDefaultFormObject();

        $validator = $formObject->getDefinition()->getField('foo')->addValidator('bar', MessagesValidator::class);
        $validator->activateAjaxUsage();

        $ajaxValidationController = $this->getAjaxValidationControllerMock($formObject);

        $ajaxValidationController->setProtectedRequestMode(false);

        $ajaxValidationController->expects($this->once())
            ->method('setUpResponseResult')
            ->willReturnCallback(function ($result) use ($expectedResult) {
                $this->assertEquals($expectedResult, $result);
            });

        $ajaxValidationController->processRequest(new Request, new Response);
    }

    /**
     * @param FormObject $formObject
     * @return AjaxValidationController|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAjaxValidationControllerMock(FormObject $formObject = null)
    {
        /** @var AjaxValidationController|\PHPUnit_Framework_MockObject_MockObject $ajaxValidationController */
        $ajaxValidationController = $this->getMockBuilder(AjaxValidationController::class)
            ->setMethods(['processRequestParent', 'setUpResponseResult', 'getForm', 'getFormObject', 'throwStatus', 'getDebugMessageForException', 'invokeMiddlewares'])
            ->getMock();

        $ajaxValidationController->method('processRequestParent')
            ->willReturnCallback(function () use ($ajaxValidationController) {
                $ajaxValidationController->runAction('foo', DefaultForm::class, 'foo', 'bar');
            });

        $form = new DefaultForm;
        $ajaxValidationController->method('getForm')
            ->willReturn($form);

        $view = $this->getMockBuilder(JsonView::class)
            ->setMethods(['render'])
            ->getMock();

        $view->method('render')
            ->willReturnCallback(function () use ($view) {
                $reflector = new ReflectionObject($view);
                $method = $reflector->getMethod('renderArray');
                $method->setAccessible(true);

                return $method->invoke($view);
            });

        $this->inject($ajaxValidationController, 'view', $view);

        $formObject = $formObject ?: $this->getDefaultFormObject();
        $formObject->setForm(new DefaultForm);

        $ajaxValidationController->method('getFormObject')
            ->willReturn($formObject);

        return $ajaxValidationController;
    }
}
