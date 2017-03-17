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
    public function initializationProperlySet()
    {
        $classes = new Classes;
        $this->assertInstanceOf(ViewClass::class, $classes->getErrors());
        $this->assertInstanceOf(ViewClass::class, $classes->getValid());
    }
}
