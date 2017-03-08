<?php
namespace Romm\Formz\Tests\Unit\ViewHelpers\Slot;

use Romm\Formz\Exceptions\ContextNotFoundException;
use Romm\Formz\Service\ViewHelper\FieldViewHelperService;
use Romm\Formz\Service\ViewHelper\SlotViewHelperService;
use Romm\Formz\Tests\Unit\UnitTestContainer;
use Romm\Formz\Tests\Unit\ViewHelpers\AbstractViewHelperUnitTest;
use Romm\Formz\ViewHelpers\Slot\RenderViewHelper;

class RenderViewHelperTest extends AbstractViewHelperUnitTest
{
    /**
     * @test
     */
    public function renderViewHelper()
    {
        $slotArgument = 'foo-slot';

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
        $slotService->expects($this->once())
            ->method('getSlotClosure')
            ->with($slotArgument)
            ->willReturn(function () {
                return 'foo';
            });

        UnitTestContainer::get()->registerMockedInstance(SlotViewHelperService::class, $slotService);

        $viewHelper = new RenderViewHelper;
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->injectFieldService($fieldService);
        $viewHelper->setArguments(['slot' => $slotArgument]);
        $viewHelper->initializeArguments();

        $this->assertEquals(
            'foo',
            $viewHelper->render()
        );
    }

    /**
     * This ViewHelper must be used from inside a `FieldViewHelper`.
     *
     * @test
     */
    public function renderViewHelperWithoutFieldThrowsException()
    {
        $viewHelper = new RenderViewHelper;
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->injectFieldService(new FieldViewHelperService);
        $viewHelper->initializeArguments();

        $this->setExpectedException(ContextNotFoundException::class);

        $viewHelper->render();
    }
}
