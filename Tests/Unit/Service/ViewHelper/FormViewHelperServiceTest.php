<?php
namespace Romm\Formz\Tests\Unit\Service\ViewHelper;

use Romm\Formz\Error\FormResult;
use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Service\ViewHelper\FormViewHelperService;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Validation\Validator\Form\DefaultFormValidator;
use TYPO3\CMS\Extbase\Mvc\Web\Request;

class FormViewHelperServiceTest extends AbstractUnitTest
{
    /**
     * Activating the form context while already having an existing context must
     * thrown an exception.
     *
     * @test
     */
    public function formContextActivatedTwiceThrowsException()
    {
        $this->setExpectedException(DuplicateEntryException::class);

        $formService = new FormViewHelperService;

        $formService->activateFormContext();
        $formService->activateFormContext();
    }

    /**
     * @test
     */
    public function activatingFormContextActivatesFormContext()
    {
        $formService = new FormViewHelperService;

        $this->assertFalse($formService->formContextExists());
        $formService->activateFormContext();
        $this->assertTrue($formService->formContextExists());
    }

    /**
     * @test
     */
    public function markingFormAsSubmittedMarksFormAsSubmitted()
    {
        $formService = new FormViewHelperService;

        $this->assertFalse($formService->formWasSubmitted());
        $formService->markFormAsSubmitted();
        $this->assertTrue($formService->formWasSubmitted());
    }

    /**
     * @test
     */
    public function setFormInstanceSetsFormInstance()
    {
        $formService = new FormViewHelperService;
        $formInstance = new DefaultForm;

        $formService->setFormInstance($formInstance);

        $this->assertSame($formInstance, $formService->getFormInstance());
    }

    /**
     * @test
     */
    public function setFormResultSetsFormResult()
    {
        $formService = new FormViewHelperService;
        $formResult = new FormResult;

        $formService->setFormResult($formResult);

        $this->assertSame($formResult, $formService->getFormResult());
    }

    /**
     * @test
     */
    public function setFormObjectSetsFormObject()
    {
        $formService = new FormViewHelperService;
        $formObject = $this->getDefaultFormObject();

        $formService->setFormObject($formObject);

        $this->assertSame($formObject, $formService->getFormObject());
    }

    /**
     * @test
     */
    public function resetStateResetsState()
    {
        $formService = new FormViewHelperService;
        $formInstance = new DefaultForm;
        $formResult = new FormResult;
        $formObject = $this->getDefaultFormObject();

        $formService->activateFormContext();
        $formService->markFormAsSubmitted();
        $formService->setFormInstance($formInstance);
        $formService->setFormResult($formResult);
        $formService->setFormObject($formObject);

        $formService->resetState();

        $this->assertFalse($formService->formContextExists());
        $this->assertFalse($formService->formWasSubmitted());
        $this->assertNull($formService->getFormInstance());
        $this->assertNull($formService->getFormResult());
        $this->assertNull($formService->getFormObject());
    }

    /**
     * After a form has been submitted, we check that the request data are
     * injected properly in the form service.
     *
     * @test
     */
    public function submittedRequestDataIsInjectedInFormService()
    {
        $formObject = $this->getDefaultFormObject();
        $formName = 'foo-bar';
        $formValidationResult = new FormResult;
        $formInstance = [
            'foo' => 'bar'
        ];

        $formObject->setLastValidationResult($formValidationResult);

        /** @var FormViewHelperService|\PHPUnit_Framework_MockObject_MockObject $formService */
        $formService = $this->getMockBuilder(FormViewHelperService::class)
            ->setMethods(['setFormInstance', 'setFormResult', 'markFormAsSubmitted'])
            ->getMock();

        $formService->setFormObject($formObject);

        $formService->expects($this->once())
            ->method('setFormInstance')
            ->with($formInstance);

        $formService->expects($this->once())
            ->method('setFormResult')
            ->with($formValidationResult);

        $formService->expects($this->once())
            ->method('markFormAsSubmitted');

        $requestMock = $this->getMockBuilder(Request::class)
            ->setMethods(['hasArgument', 'getArgument'])
            ->getMock();

        $requestMock->expects($this->once())
            ->method('hasArgument')
            ->with($formName)
            ->willReturn(true);

        $requestMock->expects($this->once())
            ->method('getArgument')
            ->with($formName)
            ->willReturn($formInstance);

        $formService->setUpData($formName, $requestMock, null);
    }

    /**
     * When the `object` option is filled in the form view helper, we check that
     * the data are injected properly in the form service.
     *
     * @test
     */
    public function formInstanceDataIsInjectedInFormService()
    {
        $formName = 'foo-bar';
        $formValidationResult = new FormResult;
        $formInstance = new DefaultForm;

        /** @var FormViewHelperService|\PHPUnit_Framework_MockObject_MockObject $formService */
        $formService = $this->getMockBuilder(FormViewHelperService::class)
            ->setMethods(['setFormInstance', 'setFormResult', 'markFormAsSubmitted', 'getFormValidator'])
            ->getMock();

        $formService->expects($this->once())
            ->method('setFormInstance')
            ->with($formInstance);

        $formService->expects($this->once())
            ->method('setFormResult')
            ->with($formValidationResult);

        $formService->expects($this->never())
            ->method('markFormAsSubmitted');

        $formService->expects($this->once())
            ->method('getFormValidator')
            ->willReturnCallback(function () use ($formValidationResult) {
                $formValidatorMock = $this->getMockBuilder(DefaultFormValidator::class)
                    ->disableOriginalConstructor()
                    ->setMethods(['validateWithoutSavingResults'])
                    ->getMock();

                $formValidatorMock->expects($this->once())
                    ->method('validateWithoutSavingResults')
                    ->willReturn($formValidationResult);

                return $formValidatorMock;
            });

        $formService->setUpData($formName, null, $formInstance);
    }
}
