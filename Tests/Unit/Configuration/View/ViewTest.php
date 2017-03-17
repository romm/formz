<?php
namespace Romm\Formz\Tests\Unit\Configuration\View;

use Romm\Formz\Configuration\View\Classes\Classes;
use Romm\Formz\Configuration\View\Layouts\LayoutGroup;
use Romm\Formz\Configuration\View\View;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class ViewTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function basicGetters()
    {
        $view = new View;

        $this->assertInstanceOf(Classes::class, $view->getClasses());

        $layout = new LayoutGroup;
        $this->assertFalse($view->hasLayout('foo'));
        $view->setLayout('foo', $layout);
        $this->assertTrue($view->hasLayout('foo'));
        $this->assertSame($layout, $view->getLayout('foo'));
        $this->assertEquals(
            ['foo' => $layout],
            $view->getLayouts()
        );
    }

    /**
     * @test
     */
    public function layoutNotFoundThrowsException()
    {
        $this->setExpectedException(EntryNotFoundException::class);

        $view = new View;
        $view->getLayout('nope');
    }

    /**
     * @test
     */
    public function absoluteLayoutPathsAreCalculated()
    {
        $path = 'foo/bar';
        $absolutePath = 'foo/bar/baz';

        /** @var View|\PHPUnit_Framework_MockObject_MockObject $view */
        $view = $this->getMockBuilder(View::class)
            ->setMethods(['getAbsolutePath'])
            ->getMock();

        $view->expects($this->once())
            ->method('getAbsolutePath')
            ->with($path)
            ->willReturn($absolutePath);

        $view->setLayoutRootPath('foo', $path);
        $this->assertEquals(
            ['foo' => $path],
            $view->getLayoutRootPaths()
        );
        $this->assertEquals(
            ['foo' => $absolutePath],
            $view->getAbsoluteLayoutRootPaths()
        );
    }

    /**
     * @test
     */
    public function absolutePartialPathsAreCalculated()
    {
        $path = 'foo/bar';
        $absolutePath = 'foo/bar/baz';

        /** @var View|\PHPUnit_Framework_MockObject_MockObject $view */
        $view = $this->getMockBuilder(View::class)
            ->setMethods(['getAbsolutePath'])
            ->getMock();

        $view->expects($this->once())
            ->method('getAbsolutePath')
            ->with($path)
            ->willReturn($absolutePath);

        $view->setPartialRootPath('foo', $path);
        $this->assertEquals(
            ['foo' => $path],
            $view->getPartialRootPaths()
        );
        $this->assertEquals(
            ['foo' => $absolutePath],
            $view->getAbsolutePartialRootPaths()
        );
    }
}
