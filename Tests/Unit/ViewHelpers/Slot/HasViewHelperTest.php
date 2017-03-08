<?php
namespace Romm\Formz\Tests\Unit\ViewHelpers\Slot;

use Romm\Formz\Service\ViewHelper\FieldViewHelperService;
use Romm\Formz\Service\ViewHelper\SlotViewHelperService;
use Romm\Formz\Tests\Unit\UnitTestContainer;
use Romm\Formz\Tests\Unit\ViewHelpers\AbstractViewHelperUnitTest;
use Romm\Formz\ViewHelpers\Slot\HasViewHelper;

class HasViewHelperTest extends AbstractViewHelperUnitTest
{
    /**
     * @test
     */
    public function renderThenViewHelper()
    {
        $slotArgument = 'foo-slot';

        /** @var HasViewHelper|\PHPUnit_Framework_MockObject_MockObject $viewHelper */
        $viewHelper = $this->getMockBuilder(HasViewHelper::class)
            ->setMethods(['renderThenChild', 'renderElseChild'])
            ->getMock();

        $viewHelper->expects($this->once())
            ->method('renderThenChild');
        $viewHelper->expects($this->never())
            ->method('renderElseChild');

        /** @var FieldViewHelperService|\PHPUnit_Framework_MockObject_MockObject $fieldService */
        $fieldService = $this->getMockBuilder(FieldViewHelperService::class)
            ->setMethods(['fieldContextExists'])
            ->getMock();
        $fieldService->expects($this->once())
            ->method('fieldContextExists')
            ->willReturn(true);

        /** @var SlotViewHelperService|\PHPUnit_Framework_MockObject_MockObject $slotService */
        $slotService = $this->getMockBuilder(SlotViewHelperService::class)
            ->setMethods(['getSlotClosure', 'hasSlotClosure'])
            ->getMock();
        $slotService->expects($this->once())
            ->method('hasSlotClosure')
            ->with($slotArgument)
            ->willReturn(true);

        UnitTestContainer::get()->registerMockedInstance(SlotViewHelperService::class, $slotService);

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->injectFieldService($fieldService);
        $viewHelper->setArguments(['slot' => $slotArgument]);
        $viewHelper->initializeArguments();

        $viewHelper->render();
    }

    /**
     * @test
     */
    public function renderElseViewHelper()
    {
        $slotArgument = 'foo-slot';

        /** @var HasViewHelper|\PHPUnit_Framework_MockObject_MockObject $viewHelper */
        $viewHelper = $this->getMockBuilder(HasViewHelper::class)
            ->setMethods(['renderThenChild', 'renderElseChild'])
            ->getMock();

        $viewHelper->expects($this->never())
            ->method('renderThenChild');
        $viewHelper->expects($this->once())
            ->method('renderElseChild');

        /** @var FieldViewHelperService|\PHPUnit_Framework_MockObject_MockObject $fieldService */
        $fieldService = $this->getMockBuilder(FieldViewHelperService::class)
            ->setMethods(['fieldContextExists'])
            ->getMock();
        $fieldService->expects($this->once())
            ->method('fieldContextExists')
            ->willReturn(true);

        /** @var SlotViewHelperService|\PHPUnit_Framework_MockObject_MockObject $slotService */
        $slotService = $this->getMockBuilder(SlotViewHelperService::class)
            ->setMethods(['getSlotClosure', 'hasSlotClosure'])
            ->getMock();
        $slotService->expects($this->once())
            ->method('hasSlotClosure')
            ->with($slotArgument)
            ->willReturn(false);

        UnitTestContainer::get()->registerMockedInstance(SlotViewHelperService::class, $slotService);

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->injectFieldService($fieldService);
        $viewHelper->setArguments(['slot' => $slotArgument]);
        $viewHelper->initializeArguments();

        $viewHelper->render();
    }
}
