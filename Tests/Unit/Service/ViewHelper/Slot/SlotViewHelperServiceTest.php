<?php

namespace Romm\Formz\Tests\Unit\Service\ViewHelper\Slot;

use Romm\Formz\Service\ViewHelper\Slot\SlotViewHelperService;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;

class SlotViewHelperServiceTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function nestedSlotEntriesWorks()
    {
        $slotService = new SlotViewHelperService;

        $closure = function () {
        };

        $slotService->activate(new RenderingContext);
        $slotService->addSlot('foo', $closure, []);
        $this->assertTrue($slotService->hasSlot('foo'));

        $slotService->activate(new RenderingContext);
        $this->assertFalse($slotService->hasSlot('foo'));
        $slotService->addSlot('bar', $closure, []);
        $this->assertTrue($slotService->hasSlot('bar'));

        $slotService->resetState();
        $this->assertFalse($slotService->hasSlot('bar'));
        $this->assertTrue($slotService->hasSlot('foo'));
    }
}
