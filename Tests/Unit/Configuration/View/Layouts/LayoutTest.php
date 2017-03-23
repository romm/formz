<?php
namespace Romm\Formz\Tests\Unit\Configuration\View\Layouts;

use Romm\Formz\Configuration\View\Layouts\Layout;
use Romm\Formz\Configuration\View\Layouts\LayoutGroup;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class LayoutTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function setLayoutSetsLayout()
    {
        $layout = new Layout;
        $layout->setLayout('foo');
        $this->assertEquals('foo', $layout->getLayout());
    }

    /**
     * @test
     */
    public function setTemplateFilesSetsTemplateFiles()
    {
        $path = 'foo/bar';
        $absolutePath = 'foo/bar/baz';

        /** @var Layout|\PHPUnit_Framework_MockObject_MockObject $layout */
        $layout = $this->getMockBuilder(Layout::class)
            ->setMethods(['getAbsolutePath'])
            ->getMock();

        $layout->expects($this->once())
            ->method('getAbsolutePath')
            ->with($path)
            ->willReturn($absolutePath);

        $layout->setTemplateFile($path);
        $this->assertEquals($absolutePath, $layout->getTemplateFile());
    }

    /**
     * @test
     */
    public function emptyTemplateFileIsFetchedFromParent()
    {
        $path = 'foo/bar';
        $absolutePath = 'foo/bar/baz';

        /** @var Layout|\PHPUnit_Framework_MockObject_MockObject $layout */
        $layout = $this->getMockBuilder(Layout::class)
            ->setMethods(['getAbsolutePath'])
            ->getMock();

        $layout->expects($this->once())
            ->method('getAbsolutePath')
            ->with($path)
            ->willReturn($absolutePath);

        $layoutGroup = new LayoutGroup;
        $layoutGroup->setTemplateFile($path);
        $layout->setParents([$layoutGroup]);

        $this->assertEquals($absolutePath, $layout->getTemplateFile());
    }
}
