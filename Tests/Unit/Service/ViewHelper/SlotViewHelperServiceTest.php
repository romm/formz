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
        $barClosure = function () {
            return 'bar';
        };

        $this->assertFalse($slotService->hasSlotClosure('foo'));
        $slotService->addSlotClosure('foo', $fooClosure);
        $this->assertTrue($slotService->hasSlotClosure('foo'));
        $this->assertSame($fooClosure, $slotService->getSlotClosure('foo'));

        $this->assertFalse($slotService->hasSlotClosure('bar'));
        $slotService->addSlotClosure('bar', $barClosure);
        $this->assertTrue($slotService->hasSlotClosure('bar'));
        $this->assertSame($barClosure, $slotService->getSlotClosure('bar'));
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

        $slotService->addSlotClosure('foo', $fooClosure);
        $slotService->resetState();

        $this->assertFalse($slotService->hasSlotClosure('foo'));
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
