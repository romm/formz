<?php
namespace Romm\Formz\Tests\Unit\ViewHelpers;

use Romm\Formz\Exceptions\ContextNotFoundException;
use Romm\Formz\Service\ViewHelper\FieldViewHelperService;
use Romm\Formz\Service\ViewHelper\SlotViewHelperService;
use Romm\Formz\Tests\Unit\UnitTestContainer;
use Romm\Formz\ViewHelpers\SlotViewHelper;

class SlotViewHelperTest extends AbstractViewHelperUnitTest
{
    /**
     * @test
     */
    public function renderViewHelper()
    {
        /** @var FieldViewHelperService|\PHPUnit_Framework_MockObject_MockObject $fieldService */
        $fieldService = $this->getMockBuilder(FieldViewHelperService::class)
            ->setMethods(['fieldContextExists'])
            ->getMock();
        $fieldService->expects($this->once())
            ->method('fieldContextExists')
            ->willReturn(true);

        /** @var SlotViewHelperService|\PHPUnit_Framework_MockObject_MockObject $slotService */
        $slotService = $this->getMockBuilder(SlotViewHelperService::class)
            ->setMethods(['addSlotClosure'])
            ->getMock();
        $slotService->expects($this->once())
            ->method('addSlotClosure');

        UnitTestContainer::get()->registerMockedInstance(SlotViewHelperService::class, $slotService);

        $viewHelper = new SlotViewHelper;
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->injectFieldService($fieldService);
        $viewHelper->initializeArguments();

        $viewHelper->render();
    }

    /**
     * This ViewHelper must be used from inside a `FieldViewHelper`.
     *
     * @test
     */
    public function renderViewHelperWithoutFieldThrowsException()
    {
        $viewHelper = new SlotViewHelper;
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->injectFieldService(new FieldViewHelperService);
        $viewHelper->initializeArguments();

        $this->setExpectedException(ContextNotFoundException::class);

        $viewHelper->render();
    }
}
