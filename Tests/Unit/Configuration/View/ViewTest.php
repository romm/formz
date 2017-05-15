<?php

namespace Romm\Formz\Tests\Unit\Configuration\View;

use Romm\Formz\Configuration\View\Classes\Classes;
use Romm\Formz\Configuration\View\View;
use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class ViewTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function addLayoutAddsLayout()
    {
        $view = new View;

        $this->assertInstanceOf(Classes::class, $view->getClasses());

        $this->assertFalse($view->hasLayout('foo'));
        $layout = $view->addLayout('foo');
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
    public function addLayoutOnFrozenConfigurationIsChecked()
    {
        $view = $this->getViewWithConfigurationFreezeStateCheck();

        $view->addLayout('foo');
    }

    /**
     * @test
     */
    public function addExistingLayoutThrowsException()
    {
        $this->setExpectedException(DuplicateEntryException::class);

        $view = new View;
        $view->addLayout('foo');
        $view->addLayout('foo');
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
    public function setLayoutRootPathSetsLayoutRootPath()
    {
        $view = new View;

        $this->assertEmpty($view->getLayoutRootPaths());
        $view->setLayoutRootPath('foo', 'bar');
        $this->assertSame(['foo' => 'bar'], $view->getLayoutRootPaths());
    }

    /**
     * @test
     */
    public function setLayoutRootPathOnFrozenConfigurationIsChecked()
    {
        $view = $this->getViewWithConfigurationFreezeStateCheck();

        $view->setLayoutRootPath('foo', 'bar');
    }

    /**
     * @test
     */
    public function setPartialRootPathSetsPartialRootPath()
    {
        $view = new View;

        $this->assertEmpty($view->getPartialRootPaths());
        $view->setPartialRootPath('foo', 'bar');
        $this->assertSame(['foo' => 'bar'], $view->getPartialRootPaths());
    }

    /**
     * @test
     */
    public function setPartialRootPathOnFrozenConfigurationIsChecked()
    {
        $view = $this->getViewWithConfigurationFreezeStateCheck();

        $view->setPartialRootPath('foo', 'bar');
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

    /**
     * @return View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getViewWithConfigurationFreezeStateCheck()
    {
        /** @var View|\PHPUnit_Framework_MockObject_MockObject $view */
        $view = $this->getMockBuilder(View::class)
            ->setMethods(['checkConfigurationFreezeState'])
            ->getMock();

        $view->expects($this->once())
            ->method('checkConfigurationFreezeState');

        return $view;
    }
}
