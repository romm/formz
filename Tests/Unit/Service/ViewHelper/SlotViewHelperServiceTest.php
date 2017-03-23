<?php
namespace Romm\Formz\Tests\Unit\Service\ViewHelper;

use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Service\ViewHelper\SlotViewHelperService;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\Core\ViewHelper\TemplateVariableContainer;

class SlotViewHelperServiceTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function addSlotAddsSlot()
    {
        $slotService = new SlotViewHelperService;
        $fooClosure = function () {
            return 'foo';
        };
        $fooArguments = ['foo' => 'bar'];
        $barClosure = function () {
            return 'bar';
        };
        $barArguments = ['bar' => 'baz'];

        $this->assertFalse($slotService->hasSlot('foo'));
        $slotService->addSlot('foo', $fooClosure, $fooArguments);
        $this->assertTrue($slotService->hasSlot('foo'));
        $this->assertSame($fooClosure, $slotService->getSlotClosure('foo'));
        $this->assertEquals($fooArguments, $slotService->getSlotArguments('foo'));

        $this->assertFalse($slotService->hasSlot('bar'));
        $slotService->addSlot('bar', $barClosure, $barArguments);
        $this->assertTrue($slotService->hasSlot('bar'));
        $this->assertSame($barClosure, $slotService->getSlotClosure('bar'));
        $this->assertEquals($barArguments, $slotService->getSlotArguments('bar'));
    }

    /**
     * Checks that arguments declared for both the slot view helper and the
     * render view helper are merged and added to the template variable
     * container.
     *
     * @test
     */
    public function templateVariablesAreAdded()
    {
        $renderingContext = $this->getMockBuilder(RenderingContext::class)
            ->setMethods(['getTemplateVariableContainer'])
            ->getMock();

        $templateVariableContainerMock = $this->getMockBuilder(TemplateVariableContainer::class)
            ->setMethods(['add', 'remove'])
            ->getMock();

        $templateVariableContainerMock->expects($this->at(0))
            ->method('add')
            ->with('bar', 'baz');
        $templateVariableContainerMock->expects($this->at(1))
            ->method('add')
            ->with('foo', 'bar');

        $renderingContext
            ->method('getTemplateVariableContainer')
            ->willReturn($templateVariableContainerMock);

        $slotService = new SlotViewHelperService;
        $emptyClosure = function () {
        };
        $slotService->addSlot('foo', $emptyClosure, ['foo' => 'bar']);

        $slotService->addTemplateVariables(
            'foo',
            ['bar' => 'baz'],
            $renderingContext
        );
    }

    /**
     * Checks that the arguments given in the render view helper are overridden
     * by the ones given in the slot view helper before being added to the
     * template variable container.
     *
     * @test
     */
    public function templateVariablesAreOverriddenThenAdded()
    {
        $renderingContext = $this->getMockBuilder(RenderingContext::class)
            ->setMethods(['getTemplateVariableContainer'])
            ->getMock();

        $templateVariableContainerMock = $this->getMockBuilder(TemplateVariableContainer::class)
            ->setMethods(['add'])
            ->getMock();

        $templateVariableContainerMock->expects($this->once())
            ->method('add')
            ->with('foo', 'bar');

        $renderingContext
            ->method('getTemplateVariableContainer')
            ->willReturn($templateVariableContainerMock);

        $slotService = new SlotViewHelperService;
        $emptyClosure = function () {
        };
        $slotService->addSlot('foo', $emptyClosure, ['foo' => 'bar']);

        $slotService->addTemplateVariables(
            'foo',
            ['foo' => 'baz'],
            $renderingContext
        );
    }

    /**
     * @test
     */
    public function templateVariablesAreProperlyRestored()
    {
        $renderingContext = $this->getMockBuilder(RenderingContext::class)
            ->setMethods(['getTemplateVariableContainer'])
            ->getMock();

        $templateVariableContainer = new TemplateVariableContainer;
        $templateVariableContainer->add('foo', 'foo');

        $renderingContext
            ->method('getTemplateVariableContainer')
            ->willReturn($templateVariableContainer);

        $slotService = new SlotViewHelperService;
        $emptyClosure = function () {
        };
        $slotService->addSlot('foo', $emptyClosure, ['foo' => 'bar']);

        $this->assertFalse($templateVariableContainer->exists('bar'));
        $this->assertEquals(
            'foo',
            $templateVariableContainer->get('foo')
        );

        $slotService->addTemplateVariables('foo', ['bar' => 'baz'], $renderingContext);

        $this->assertTrue($templateVariableContainer->exists('bar'));
        $this->assertEquals(
            'bar',
            $templateVariableContainer->get('foo')
        );

        $slotService->restoreTemplateVariables('foo', $renderingContext);

        $this->assertFalse($templateVariableContainer->exists('bar'));
        $this->assertEquals(
            'foo',
            $templateVariableContainer->get('foo')
        );
    }

    /**
     * @test
     */
    public function resetStateResetsState()
    {
        $slotService = new SlotViewHelperService;
        $fooClosure = function () {
            return 'foo';
        };

        $slotService->addSlot('foo', $fooClosure, []);
        $slotService->resetState();

        $this->assertFalse($slotService->hasSlot('foo'));
    }

    /**
     * @test
     */
    public function getNotFoundSlotClosureThrowsException()
    {
        $this->setExpectedException(EntryNotFoundException::class);

        $slotService = new SlotViewHelperService;
        $slotService->getSlotClosure('bar');
    }

    /**
     * @test
     */
    public function getNotFoundSlotArgumentsThrowsException()
    {
        $this->setExpectedException(EntryNotFoundException::class);

        $slotService = new SlotViewHelperService;
        $slotService->getSlotArguments('bar');
    }
}
