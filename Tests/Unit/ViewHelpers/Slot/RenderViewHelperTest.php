<?php
namespace Romm\Formz\Tests\Unit\ViewHelpers\Slot;

use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Exceptions\ContextNotFoundException;
use Romm\Formz\Service\ViewHelper\Field\FieldViewHelperService;
use Romm\Formz\Service\ViewHelper\Slot\SlotViewHelperService;
use Romm\Formz\Tests\Unit\UnitTestContainer;
use Romm\Formz\Tests\Unit\ViewHelpers\AbstractViewHelperUnitTest;
use Romm\Formz\ViewHelpers\Slot\RenderViewHelper;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;

class RenderViewHelperTest extends AbstractViewHelperUnitTest
{
    /**
     * @test
     */
    public function renderViewHelper()
    {
        $slotName = 'foo-slot';

        /** @var FieldViewHelperService|\PHPUnit_Framework_MockObject_MockObject $fieldService */
        $fieldService = $this->getMockBuilder(FieldViewHelperService::class)
            ->setMethods(['fieldContextExists'])
            ->getMock();
        $fieldService->expects($this->once())
            ->method('fieldContextExists')
            ->willReturn(true);

        /** @var SlotViewHelperService|\PHPUnit_Framework_MockObject_MockObject $slotService */
        $slotService = $this->getMockBuilder(SlotViewHelperService::class)
            ->setMethods(['getSlotClosure', 'hasSlot', 'addTemplateVariables', 'restoreTemplateVariables'])
            ->getMock();
        $slotService->expects($this->once())
            ->method('hasSlot')
            ->with($slotName)
            ->willReturn(true);
        $slotService->expects($this->once())
            ->method('getSlotClosure')
            ->with($slotName)
            ->willReturn(function () {
                return 'foo';
            });
        $slotService->expects($this->once())
            ->method('addTemplateVariables')
            ->with($slotName)
            ->willReturn([]);
        $slotService->expects($this->once())
            ->method('restoreTemplateVariables')
            ->with($slotName)
            ->willReturn([]);

        UnitTestContainer::get()->registerMockedInstance(SlotViewHelperService::class, $slotService);

        $viewHelper = new RenderViewHelper;
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->injectFieldService($fieldService);
        $viewHelper->setArguments([
            'slot'      => $slotName,
            'arguments' => []
        ]);
        $viewHelper->initializeArguments();

        $this->assertEquals(
            'foo',
            $viewHelper->render()
        );
    }

    /**
     * @test
     */
    public function argumentsAreAddedThenRemoved()
    {
        /** @var SlotViewHelperService|\PHPUnit_Framework_MockObject_MockObject $slotService */
        $slotService = $this->getMockBuilder(SlotViewHelperService::class)
            ->setMethods(['addTemplateVariables', 'restoreTemplateVariables'])
            ->getMock();
        $emptyClosure = function () {
        };
        $slotService->activate(new RenderingContext);
        $slotService->addSlot('foo', $emptyClosure, ['foo' => 'bar']);

        $slotService->expects($this->once())
            ->method('addTemplateVariables');
        $slotService->expects($this->once())
            ->method('restoreTemplateVariables');

        UnitTestContainer::get()->registerMockedInstance(SlotViewHelperService::class, $slotService);

        $viewHelper = new RenderViewHelper;
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->setArguments([
            'slot'      => 'foo',
            'arguments' => []
        ]);
        $viewHelper->initializeArguments();

        $fieldService = new FieldViewHelperService;
        $fieldService->setCurrentField(new Field);
        $viewHelper->injectFieldService($fieldService);

        $viewHelper->render();
    }

    /**
     * This ViewHelper must be used from inside a `FieldViewHelper`.
     *
     * @test
     */
    public function renderViewHelperWithoutFieldThrowsException()
    {
        $this->setExpectedException(ContextNotFoundException::class);

        $viewHelper = new RenderViewHelper;
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->injectFieldService(new FieldViewHelperService);
        $viewHelper->initializeArguments();

        $viewHelper->render();
    }
}
