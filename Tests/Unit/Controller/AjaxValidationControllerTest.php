<?php
namespace Romm\Formz\Tests\Unit\Controller;

use Prophecy\Prophecy\ObjectProphecy;
use ReflectionObject;
use Romm\Formz\Configuration\Form\Field\Validation\Validation;
use Romm\Formz\Controller\AjaxValidationController;
use Romm\Formz\Core\Core;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Exceptions\InvalidConfigurationException;
use Romm\Formz\Exceptions\MissingArgumentException;
use Romm\Formz\Form\FormObject;
use Romm\Formz\Service\ContextService;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Fixture\Validation\Validator\DummyValidator;
use Romm\Formz\Tests\Fixture\Validation\Validator\ExceptionDummyValidator;
use Romm\Formz\Tests\Fixture\Validation\Validator\MessagesValidator;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Mvc\View\JsonView;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationBuilder;

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
     * If an argument is missing in the HTTP request, an exception should be
     * thrown.
     *
     * @param array $arguments
     * @test
     * @dataProvider missingArgumentThrowsExceptionDataProvider
     */
    public function missingArgumentThrowsException(array $arguments)
    {
        $this->setExpectedException(MissingArgumentException::class);

        /** @var AjaxValidationController|\PHPUnit_Framework_MockObject_MockObject $ajaxValidationController */
        $ajaxValidationController = $this->getMockBuilder(AjaxValidationController::class)
            ->setMethods(['getArgument'])
            ->getMock();

        $i = 0;

        foreach ($arguments as $argument) {
            $ajaxValidationController->expects($this->at($i++))
                ->method('getArgument')
                ->with($argument)
                ->willReturn('foo');
        }

        $ajaxValidationController->setProtectedRequestMode(false);
        $ajaxValidationController->runAction();
    }

    /**
     * @return array
     */
    public function missingArgumentThrowsExceptionDataProvider()
    {
        $finalArguments = [];
        $lastArgumentsList = [];
        $requiredArguments = AjaxValidationController::$requiredArguments;
        array_pop($requiredArguments);

        foreach ($requiredArguments as $argument) {
            $lastArgumentsList = array_merge($lastArgumentsList, [$argument]);
            $finalArguments[] = [$lastArgumentsList];
        }

        return $finalArguments;
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

        $ajaxValidationController->runAction();

        $ajaxValidationController->expects($this->never())
            ->method('getDebugMessageForException');

        $result = $ajaxValidationController->getView()->render();

        $this->assertFalse($result['success']);
        $this->assertEquals(
            ContextService::get()->translate(AjaxValidationController::DEFAULT_ERROR_MESSAGE_KEY),
            $result['messages']['errors']['default']
        );
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
        $validation = new Validation;
        $validation->setClassName(ExceptionDummyValidator::class);
        $validation->setName('bar');
        $validation->activateAjaxUsage();
        $formObject->getConfiguration()->getField('foo')->addValidation($validation);

        $ajaxValidationController = $this->getAjaxValidationControllerMockWithArgumentsHandling([], $formObject);

        $ajaxValidationController->expects($this->once())
            ->method('getDebugMessageForException');

        $this->setExtensionConfigurationValue('debugMode', true);

        $ajaxValidationController->runAction();
        $result = $ajaxValidationController->getView()->render();

        $this->assertFalse($result['success']);
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
        $formObject->getConfigurationValidationResult()->addError(new Error('foo', 42));

        $ajaxValidationController = $this->getAjaxValidationControllerMockWithArgumentsHandling([], $formObject);

        $ajaxValidationController->setProtectedRequestMode(false);
        $ajaxValidationController->runAction();
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

        $ajaxValidationController = $this->getAjaxValidationControllerMockWithArgumentsHandling([AjaxValidationController::ARGUMENT_FIELD_NAME => 'unknown']);

        $ajaxValidationController->setProtectedRequestMode(false);
        $ajaxValidationController->runAction();
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
        $ajaxValidationController = $this->getAjaxValidationControllerMockWithArgumentsHandling();

        $ajaxValidationController->setProtectedRequestMode(false);
        $ajaxValidationController->runAction();
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
        $validation = new Validation;
        $validation->setName('bar');
        $formObject->getConfiguration()->getField('foo')->addValidation($validation);

        $ajaxValidationController = $this->getAjaxValidationControllerMockWithArgumentsHandling([], $formObject);

        $ajaxValidationController->setProtectedRequestMode(false);
        $ajaxValidationController->runAction();
    }

    /**
     * @test
     */
    public function validationWithValidValueWorks()
    {
        $expectedResult = [
            'success' => true,
            'messages' => []
        ];

        $formObject = $this->getDefaultFormObject();
        $validation = new Validation;
        $validation->setClassName(DummyValidator::class);
        $validation->setName('bar');
        $validation->activateAjaxUsage();
        $formObject->getConfiguration()->getField('foo')->addValidation($validation);

        $ajaxValidationController = $this->getAjaxValidationControllerMockWithArgumentsHandling([], $formObject);

        $ajaxValidationController->setProtectedRequestMode(false);
        $ajaxValidationController->runAction();
        $result = $ajaxValidationController->getView()->render();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Checks that errors, warnings and notices are added to the result array.
     *
     * @test
     */
    public function messagesTypesAreReturnedInResult()
    {
        $expectedResult = [
            'success' => false,
            'messages' => [
                'errors' => [MessagesValidator::MESSAGE_1 => MessagesValidator::MESSAGE_1],
                'warnings' => [MessagesValidator::MESSAGE_2 => MessagesValidator::MESSAGE_2],
                'notices' => [MessagesValidator::MESSAGE_3 => MessagesValidator::MESSAGE_3]
            ]
        ];

        $formObject = $this->getDefaultFormObject();

        $messagesValidator = new Validation;
        $messagesValidator->setClassName(MessagesValidator::class);
        $messagesValidator->setName('bar');
        $messagesValidator->activateAjaxUsage();
        $formObject->getConfiguration()->getField('foo')->addValidation($messagesValidator);

        $ajaxValidationController = $this->getAjaxValidationControllerMockWithArgumentsHandling([], $formObject);

        $ajaxValidationController->setProtectedRequestMode(false);
        $ajaxValidationController->runAction();
        $result = $ajaxValidationController->getView()->render();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param FormObject $formObject
     * @return AjaxValidationController|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAjaxValidationControllerMock(FormObject $formObject = null)
    {
        /** @var AjaxValidationController|\PHPUnit_Framework_MockObject_MockObject $ajaxValidationController */
        $ajaxValidationController = $this->getMockBuilder(AjaxValidationController::class)
            ->setMethods(['getArgument', 'getFormObject', 'getPropertyMapper', 'throwStatus', 'getDebugMessageForException'])
            ->getMock();

        /** @var PropertyMapper $propertyMapper */
        $propertyMapper = new PropertyMapper;
        $this->inject($propertyMapper, 'objectManager', Core::get()->getObjectManager());
        $this->inject($propertyMapper, 'configurationBuilder', new PropertyMappingConfigurationBuilder);
        $propertyMapper->initializeObject();

        $ajaxValidationController->method('getPropertyMapper')
            ->willReturn($propertyMapper);

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

        $ajaxValidationController->method('getFormObject')
            ->willReturn($formObject);

        return $ajaxValidationController;
    }

    /**
     * @param array           $arguments
     * @param FormObject|null $formObject
     * @return AjaxValidationController|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAjaxValidationControllerMockWithArgumentsHandling(array $arguments = [], FormObject $formObject = null)
    {
        $ajaxValidationController = $this->getAjaxValidationControllerMock($formObject);

        $defaultArguments = [
            AjaxValidationController::ARGUMENT_FORM_CLASS_NAME => DefaultForm::class,
            AjaxValidationController::ARGUMENT_FORM_NAME       => 'foo',
            AjaxValidationController::ARGUMENT_FORM            => [
                'tx_my_form' => [
                    ['foo' => 'bar']
                ]
            ],
            AjaxValidationController::ARGUMENT_FIELD_NAME      => 'foo',
            AjaxValidationController::ARGUMENT_VALIDATOR_NAME  => 'bar'
        ];

        $i = 0;
        $arguments = array_merge($defaultArguments, $arguments);

        foreach ($arguments as $key => $value) {
            $ajaxValidationController->expects($this->at($i++))
                ->method('getArgument')
                ->with($key)
                ->willReturn($value);
        }

        return $ajaxValidationController;
    }
}
