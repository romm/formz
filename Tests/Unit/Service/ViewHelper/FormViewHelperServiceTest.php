<?php
namespace Romm\Formz\Tests\Unit\Service\ViewHelper;

use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Service\ViewHelper\FormViewHelperService;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

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
}
