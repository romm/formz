<?php
namespace Romm\Formz\Tests\Unit\Service\ViewHelper;

use Romm\Formz\Service\ViewHelper\SectionViewHelperService;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class SectionViewHelperServiceTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function addSectionClosureAddsSectionClosure()
    {
        $sectionService = new SectionViewHelperService;
        $fooClosure = function () {
            return 'foo';
        };
        $barClosure = function () {
            return 'bar';
        };

        $sectionService->addSectionClosure('foo', $fooClosure);
        $sectionService->addSectionClosure('bar', $barClosure);

        $this->assertSame($fooClosure, $sectionService->getSectionClosure('foo'));
        $this->assertSame($barClosure, $sectionService->getSectionClosure('bar'));
    }

    /**
     * @test
     */
    public function resetStateResetsState()
    {
        $sectionService = new SectionViewHelperService;
        $fooClosure = function () {
            return 'foo';
        };

        $sectionService->addSectionClosure('foo', $fooClosure);
        $sectionService->resetState();

        $this->assertNull($sectionService->getSectionClosure('foo'));
    }
}
