<?php
namespace Romm\Formz\Tests\Unit\Service\ViewHelper;

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

        $slotService->addSlotClosure('foo', $fooClosure);
        $slotService->addSlotClosure('bar', $barClosure);

        $this->assertSame($fooClosure, $slotService->getSlotClosure('foo'));
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

        $this->assertNull($slotService->getSlotClosure('foo'));
    }
}
