<?php
namespace Romm\Formz\Tests\Unit\Configuration\View\Layouts;

use Romm\Formz\Configuration\View\Layouts\LayoutGroup;
use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class LayoutGroupTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function addItemAddsItem()
    {
        $layoutGroup = new LayoutGroup('foo');

        $this->assertFalse($layoutGroup->hasItem('foo'));
        $layout = $layoutGroup->addItem('foo');
        $this->assertTrue($layoutGroup->hasItem('foo'));
        $this->assertEquals($layout, $layoutGroup->getItem('foo'));
        $this->assertEquals(['foo' => $layout], $layoutGroup->getItems());
    }

    /**
     * @test
     */
    public function addItemOnFrozenConfigurationIsChecked()
    {
        $layoutGroup = $this->getLayoutGroupWithConfigurationFreezeStateCheck();

        $layoutGroup->addItem('foo');
    }

    /**
     * @test
     */
    public function addExistingItemThrowsException()
    {
        $this->setExpectedException(DuplicateEntryException::class);

        $layoutGroup = new LayoutGroup('foo');
        $layoutGroup->addItem('foo');
        $layoutGroup->addItem('foo');
    }

    /**
     * @test
     */
    public function getUnknownItemThrowsException()
    {
        $this->setExpectedException(EntryNotFoundException::class);

        $layoutGroup = new LayoutGroup('foo');
        $layoutGroup->getItem('nope');
    }

    /**
     * @test
     */
    public function setTemplateFileSetsTemplateFile()
    {
        $layoutGroup = new LayoutGroup('foo');
        $layoutGroup->setTemplateFile('foo/bar');
        $this->assertEquals('foo/bar', $layoutGroup->getTemplateFile());
    }

    /**
     * @test
     */
    public function setTemplateFileOnFrozenConfigurationIsChecked()
    {
        $layoutGroup = $this->getLayoutGroupWithConfigurationFreezeStateCheck();

        $layoutGroup->setTemplateFile('foo/bar');
    }

    /**
     * @return LayoutGroup|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLayoutGroupWithConfigurationFreezeStateCheck()
    {
        /** @var LayoutGroup|\PHPUnit_Framework_MockObject_MockObject $layoutGroup */
        $layoutGroup = $this->getMockBuilder(LayoutGroup::class)
            ->setConstructorArgs(['foo'])
            ->setMethods(['checkConfigurationFreezeState'])
            ->getMock();

        $layoutGroup->expects($this->once())
            ->method('checkConfigurationFreezeState');

        return $layoutGroup;
    }
}
