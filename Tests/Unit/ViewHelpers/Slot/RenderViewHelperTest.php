<?php
namespace Romm\Formz\Tests\Unit\ViewHelpers\Slot;

use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Exceptions\ContextNotFoundException;
use Romm\Formz\Service\ViewHelper\FieldViewHelperService;
use Romm\Formz\Service\ViewHelper\SlotViewHelperService;
use Romm\Formz\Tests\Unit\UnitTestContainer;
use Romm\Formz\Tests\Unit\ViewHelpers\AbstractViewHelperUnitTest;
use Romm\Formz\ViewHelpers\Slot\RenderViewHelper;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\Core\ViewHelper\TemplateVariableContainer;

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
            ->setMethods(['getSlotClosure', 'getSlotArguments', 'hasSlot'])
            ->getMock();
        $slotService->expects($this->once())
            ->method('hasSlot')
            ->with($slotArgument)
            ->willReturn(true);
        $slotService->expects($this->once())
            ->method('getSlotClosure')
            ->with($slotArgument)
            ->willReturn(function () {
                return 'foo';
            });
        $slotService->expects($this->once())
            ->method('getSlotArguments')
            ->with($slotArgument)
            ->willReturn([]);

        UnitTestContainer::get()->registerMockedInstance(SlotViewHelperService::class, $slotService);

        $viewHelper = new RenderViewHelper;
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->injectFieldService($fieldService);
        $viewHelper->setArguments([
            'slot'      => $slotArgument,
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
        $this->renderingContext = $this->getMockBuilder(RenderingContext::class)
            ->setMethods(['getTemplateVariableContainer'])
            ->getMock();

        $templateVariableContainerMock = $this->getMockBuilder(TemplateVariableContainer::class)
            ->setMethods(['add', 'remove'])
            ->getMock();

        $templateVariableContainerMock->expects($this->once())
            ->method('add')
            ->with('foo', 'bar');
        $templateVariableContainerMock->expects($this->once())
            ->method('remove')
            ->with('foo');

        $this->renderingContext
            ->method('getTemplateVariableContainer')
            ->willReturn($templateVariableContainerMock);

        $slotService = new SlotViewHelperService;
        $emptyClosure = function () {
        };
        $slotService->addSlot('foo', $emptyClosure, ['foo' => 'bar']);

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
     * Arguments defined in the `SlotViewHelper` should override the ones
     * defined in this view helper.
     *
     * @test
     */
    public function argumentsAreOverriddenBySlot()
    {
        $this->renderingContext = $this->getMockBuilder(RenderingContext::class)
            ->setMethods(['getTemplateVariableContainer'])
            ->getMock();

        $templateVariableContainerMock = $this->getMockBuilder(TemplateVariableContainer::class)
            ->setMethods(['add', 'remove'])
            ->getMock();

        $templateVariableContainerMock->expects($this->once())
            ->method('add')
            ->with('foo', 'baz');
        $templateVariableContainerMock->expects($this->once())
            ->method('remove')
            ->with('foo');

        $this->renderingContext
            ->method('getTemplateVariableContainer')
            ->willReturn($templateVariableContainerMock);

        $slotService = new SlotViewHelperService;
        $emptyClosure = function () {
        };
        $slotService->addSlot('foo', $emptyClosure, ['foo' => 'baz']);

        UnitTestContainer::get()->registerMockedInstance(SlotViewHelperService::class, $slotService);

        $viewHelper = new RenderViewHelper;
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->setArguments([
            'slot'      => 'foo',
            'arguments' => ['foo' => 'bar']
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
