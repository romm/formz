<?php
namespace Romm\Formz\Tests\Unit\Service\ViewHelper;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Romm\Formz\AssetHandler\Html\DataAttributesAssetHandler;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Form\FormObject\FormObjectProxy;
use Romm\Formz\Service\ViewHelper\FormViewHelperService;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Validation\Validator\Form\DefaultFormValidator;

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
        $formObject = $this->getDefaultFormObject();

        $formService->activateFormContext();
        $formService->setFormObject($formObject);

        $formService->resetState();

        $this->assertFalse($formService->formContextExists());
        $this->assertNull($formService->getFormObject());
    }

    /**
     * The values data attributes must not be added when a form object does not
     * contain a form instance.
     *
     * @test
     */
    public function noDataAttributesAreNotAddedWhenNoForm()
    {
        $formService = new FormViewHelperService;
        $formService->activateFormContext();
        $formService->setFormObject($this->getDefaultFormObject());

        /** @var DataAttributesAssetHandler|ObjectProphecy $dataAttributesAssetHandler */
        $dataAttributesAssetHandler = $this->prophesize(DataAttributesAssetHandler::class);

        $dataAttributesAssetHandler->getFieldsValuesDataAttributes()->shouldNotBeCalled();
        $dataAttributesAssetHandler->getFieldSubmissionDoneDataAttribute()->shouldNotBeCalled();
        $dataAttributesAssetHandler->getFieldsValidDataAttributes()->shouldNotBeCalled();
        $dataAttributesAssetHandler->getFieldsMessagesDataAttributes()->shouldNotBeCalled();

        $formService->getDataAttributes($dataAttributesAssetHandler->reveal());
    }

    /**
     * When a form has been validated, the values data attributes must be
     * fetched with its form result instance.
     *
     * @test
     */
    public function validatedFormValuesDataAttributesAreAddedWithFormResultInstance()
    {
        $expectedDataAttributes = ['foo' => 'bar'];

        $formObject = $this->getDefaultFormObject(function (FormObjectProxy $proxy) {
            $proxy->markFormAsValidated();
        });
        $formObject->setForm(new DefaultForm);

        $formService = new FormViewHelperService;
        $formService->activateFormContext();
        $formService->setFormObject($formObject);

        $formResult = $formObject->getFormResult();

        /** @var DataAttributesAssetHandler|ObjectProphecy $dataAttributesAssetHandler */
        $dataAttributesAssetHandler = $this->prophesize(DataAttributesAssetHandler::class);
        $dataAttributesAssetHandler->getFieldsValuesDataAttributes(Argument::is($formResult))
            ->willReturn($expectedDataAttributes);

        $dataAttributesAssetHandler->getFieldsValidDataAttributes()->willReturn([]);
        $dataAttributesAssetHandler->getFieldsMessagesDataAttributes()->willReturn([]);

        $dataAttributes = $formService->getDataAttributes($dataAttributesAssetHandler->reveal());

        $this->assertSame($expectedDataAttributes, $dataAttributes);
    }

    /**
     * When a form was not validated, in order to fetch its fields values data
     * attributes a new form validator is used to get a new form result. This
     * form validator should not have any impact on the form object proxy, but
     * still return a valid form result.
     *
     * @test
     */
    public function notValidatedFormValuesDataAttributesAreAddedWithNewFormResultInstance()
    {
        $expectedDataAttributes = ['foo' => 'bar'];

        $formObject = $this->getDefaultFormObject();
        $formObject->setForm(new DefaultForm);

        /** @var FormViewHelperService|\PHPUnit_Framework_MockObject_MockObject $formServiceMock */
        $formServiceMock = $this->getMockBuilder(FormViewHelperService::class)
            ->setMethods(['getFormValidator'])
            ->getMock();

        $formServiceMock->activateFormContext();
        $formServiceMock->setFormObject($formObject);

        $formResult = new FormResult;

        $formServiceMock->expects($this->once())
            ->method('getFormValidator')
            ->willReturnCallback(function () use ($formResult) {
                $formValidatorMock = $this->getMockBuilder(DefaultFormValidator::class)
                    ->disableOriginalConstructor()
                    ->getMock();
                $formValidatorMock->expects($this->once())
                    ->method('validate')
                    ->willReturn($formResult);

                return $formValidatorMock;
            });

        /** @var DataAttributesAssetHandler|ObjectProphecy $dataAttributesAssetHandler */
        $dataAttributesAssetHandler = $this->prophesize(DataAttributesAssetHandler::class);
        $dataAttributesAssetHandler->getFieldsValuesDataAttributes(Argument::is($formResult))
            ->willReturn($expectedDataAttributes);

        $dataAttributesAssetHandler->getFieldsValidDataAttributes()->willReturn([]);
        $dataAttributesAssetHandler->getFieldsMessagesDataAttributes()->willReturn([]);

        $dataAttributes = $formServiceMock->getDataAttributes($dataAttributesAssetHandler->reveal());

        $this->assertSame($expectedDataAttributes, $dataAttributes);
    }

    /**
     * When the form was submitted, the "submission" data attribute must be
     * added.
     *
     * @test
     */
    public function submittedFormDataAttributeIsAdded()
    {
        $expectedDataAttributes = ['foo' => 'bar'];

        $formObject = $this->getDefaultFormObject(function (FormObjectProxy $proxy) {
            $proxy->markFormAsSubmitted();
        });
        $formObject->setForm(new DefaultForm);

        /** @var FormViewHelperService|\PHPUnit_Framework_MockObject_MockObject $formService */
        $formService = $this->getMockBuilder(FormViewHelperService::class)
            ->setMethods(['getFormValidationResult'])
            ->getMock();

        $formService->method('getFormValidationResult')
            ->willReturn(new FormResult);

        $formService->activateFormContext();
        $formService->setFormObject($formObject);

        /** @var DataAttributesAssetHandler|ObjectProphecy $dataAttributesAssetHandler */
        $dataAttributesAssetHandler = $this->prophesize(DataAttributesAssetHandler::class);
        $dataAttributesAssetHandler->getFieldSubmissionDoneDataAttribute()
            ->shouldBeCalled()
            ->willReturn($expectedDataAttributes);

        $dataAttributesAssetHandler->getFieldsValuesDataAttributes(Argument::any())->willReturn([]);

        $dataAttributes = $formService->getDataAttributes($dataAttributesAssetHandler->reveal());

        $this->assertSame($expectedDataAttributes, $dataAttributes);
    }

    /**
     * When the field was validated, the fields valid data attributes must be
     * added.
     *
     * @test
     */
    public function fieldsValidDataAttributesAreAdded()
    {
        $expectedDataAttributes = ['foo' => 'bar'];

        $formObject = $this->getDefaultFormObject(function (FormObjectProxy $proxy) {
            $proxy->markFormAsValidated();
        });
        $formObject->setForm(new DefaultForm);

        $formService = new FormViewHelperService;
        $formService->activateFormContext();
        $formService->setFormObject($formObject);

        /** @var DataAttributesAssetHandler|ObjectProphecy $dataAttributesAssetHandler */
        $dataAttributesAssetHandler = $this->prophesize(DataAttributesAssetHandler::class);
        $dataAttributesAssetHandler->getFieldsValidDataAttributes()
            ->shouldBeCalled()
            ->willReturn($expectedDataAttributes);

        $dataAttributesAssetHandler->getFieldsValuesDataAttributes(Argument::any())->willReturn([]);
        $dataAttributesAssetHandler->getFieldsMessagesDataAttributes()->willReturn([]);

        $dataAttributes = $formService->getDataAttributes($dataAttributesAssetHandler->reveal());

        $this->assertSame($expectedDataAttributes, $dataAttributes);
    }

    /**
     * When the field was validated, the fields messages data attributes must be
     * added.
     *
     * @test
     */
    public function fieldsMessagesDataAttributesAreAdded()
    {
        $expectedDataAttributes = ['foo' => 'bar'];

        $formObject = $this->getDefaultFormObject(function (FormObjectProxy $proxy) {
            $proxy->markFormAsValidated();
        });
        $formObject->setForm(new DefaultForm);

        $formService = new FormViewHelperService;
        $formService->activateFormContext();
        $formService->setFormObject($formObject);

        /** @var DataAttributesAssetHandler|ObjectProphecy $dataAttributesAssetHandler */
        $dataAttributesAssetHandler = $this->prophesize(DataAttributesAssetHandler::class);
        $dataAttributesAssetHandler->getFieldsMessagesDataAttributes()
            ->shouldBeCalled()
            ->willReturn($expectedDataAttributes);

        $dataAttributesAssetHandler->getFieldsValuesDataAttributes(Argument::any())->willReturn([]);
        $dataAttributesAssetHandler->getFieldsValidDataAttributes()->willReturn([]);

        $dataAttributes = $formService->getDataAttributes($dataAttributesAssetHandler->reveal());

        $this->assertSame($expectedDataAttributes, $dataAttributes);
    }
}
