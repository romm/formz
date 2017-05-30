<?php
namespace Romm\Formz\Tests\Unit\Service\ViewHelper\Slot;

use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Service\ViewHelper\Slot\SlotContextEntry;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\Core\ViewHelper\TemplateVariableContainer;

class SlotContextEntryTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function addSlotAddsSlot()
    {
        $contextEntry = new SlotContextEntry(new RenderingContext);
        $fooClosure = function () {
            return 'foo';
        };
        $fooArguments = ['foo' => 'bar'];
        $barClosure = function () {
            return 'bar';
        };
        $barArguments = ['bar' => 'baz'];

        $this->assertFalse($contextEntry->hasSlot('foo'));
        $contextEntry->addSlot('foo', $fooClosure, $fooArguments);
        $this->assertTrue($contextEntry->hasSlot('foo'));
        $this->assertSame($fooClosure, $contextEntry->getSlotClosure('foo'));
        $this->assertEquals($fooArguments, $contextEntry->getSlotArguments('foo'));

        $this->assertFalse($contextEntry->hasSlot('bar'));
        $contextEntry->addSlot('bar', $barClosure, $barArguments);
        $this->assertTrue($contextEntry->hasSlot('bar'));
        $this->assertSame($barClosure, $contextEntry->getSlotClosure('bar'));
        $this->assertEquals($barArguments, $contextEntry->getSlotArguments('bar'));
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
        /** @var RenderingContext|\PHPUnit_Framework_MockObject_MockObject $renderingContext */
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

        $contextEntry = new SlotContextEntry($renderingContext);
        $emptyClosure = function () {
        };
        $contextEntry->addSlot('foo', $emptyClosure, ['foo' => 'bar']);

        $contextEntry->addTemplateVariables(
            'foo',
            ['bar' => 'baz']
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
        /** @var RenderingContext|\PHPUnit_Framework_MockObject_MockObject $renderingContext */
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

        $contextEntry = new SlotContextEntry($renderingContext);
        $emptyClosure = function () {
        };
        $contextEntry->addSlot('foo', $emptyClosure, ['foo' => 'bar']);

        $contextEntry->addTemplateVariables(
            'foo',
            ['foo' => 'baz']
        );
    }

    /**
     * @test
     */
    public function templateVariablesAreProperlyRestored()
    {
        /** @var RenderingContext|\PHPUnit_Framework_MockObject_MockObject $renderingContext */
        $renderingContext = $this->getMockBuilder(RenderingContext::class)
            ->setMethods(['getTemplateVariableContainer'])
            ->getMock();

        $templateVariableContainer = new TemplateVariableContainer;
        $templateVariableContainer->add('foo', 'foo');

        $renderingContext
            ->method('getTemplateVariableContainer')
            ->willReturn($templateVariableContainer);

        $contextEntry = new SlotContextEntry($renderingContext);
        $emptyClosure = function () {
        };
        $contextEntry->addSlot('foo', $emptyClosure, ['foo' => 'bar']);

        $this->assertFalse($templateVariableContainer->exists('bar'));
        $this->assertEquals(
            'foo',
            $templateVariableContainer->get('foo')
        );

        $contextEntry->addTemplateVariables('foo', ['bar' => 'baz']);

        $this->assertTrue($templateVariableContainer->exists('bar'));
        $this->assertEquals(
            'bar',
            $templateVariableContainer->get('foo')
        );

        $contextEntry->restoreTemplateVariables('foo');

        $this->assertFalse($templateVariableContainer->exists('bar'));
        $this->assertEquals(
            'foo',
            $templateVariableContainer->get('foo')
        );
    }

    /**
     * @test
     */
    public function getNotFoundSlotClosureThrowsException()
    {
        $this->setExpectedException(EntryNotFoundException::class);

        $contextEntry = new SlotContextEntry(new RenderingContext);
        $contextEntry->getSlotClosure('bar');
    }

    /**
     * @test
     */
    public function getNotFoundSlotArgumentsThrowsException()
    {
        $this->setExpectedException(EntryNotFoundException::class);

        $contextEntry = new SlotContextEntry(new RenderingContext);
        $contextEntry->getSlotArguments('bar');
    }
}
