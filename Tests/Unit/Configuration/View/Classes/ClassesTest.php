<?php
namespace Romm\Formz\Tests\Unit\Configuration\View\Classes;

use Romm\Formz\Configuration\View\Classes\Classes;
use Romm\Formz\Configuration\View\Classes\ViewClass;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class ClassesTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function initializationDoneProperly()
    {
        $classes = new Classes;
        $this->assertInstanceOf(ViewClass::class, $classes->getErrorsClasses());
        $this->assertInstanceOf(ViewClass::class, $classes->getValidClasses());
    }
}
