<?php
namespace Romm\Formz\Tests\Unit\Configuration\View\Layouts;

use Romm\Formz\Configuration\View\Layouts\Layout;
use Romm\Formz\Configuration\View\Layouts\LayoutGroup;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class LayoutGroupTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function setItemSetsItem()
    {
        $layoutGroup = new LayoutGroup;

        $layout = new Layout;
        $this->assertFalse($layoutGroup->hasItem('foo'));
        $layoutGroup->setItem('foo', $layout);
        $this->assertTrue($layoutGroup->hasItem('foo'));
        $this->assertEquals($layout, $layoutGroup->getItem('foo'));
        $this->assertEquals(['foo' => $layout], $layoutGroup->getItems());
    }

    /**
     * @test
     */
    public function getUnknownItemThrowsException()
    {
        $this->setExpectedException(EntryNotFoundException::class);

        $layoutGroup = new LayoutGroup;
        $layoutGroup->getItem('nope');
    }

    /**
     * @test
     */
    public function setTemplateFileSetsTemplateFile()
    {
        $layoutGroup = new LayoutGroup;
        $layoutGroup->setTemplateFile('foo/bar');
        $this->assertEquals('foo/bar', $layoutGroup->getTemplateFile());
    }
}
