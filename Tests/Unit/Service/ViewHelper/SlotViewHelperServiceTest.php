<?php
namespace Romm\Formz\Tests\Unit\Service\ViewHelper;

use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Service\ViewHelper\SlotViewHelperService;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class SlotViewHelperServiceTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function addSlotClosureAddsSlotClosure()
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
}
