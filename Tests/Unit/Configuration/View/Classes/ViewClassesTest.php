<?php
namespace Romm\Formz\Tests\Unit\Configuration\View\Classes;

use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\Formz\Configuration\View\Classes\ViewClass;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class ViewClassesTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function setItemSetsItem()
    {
        $viewClass = new ViewClass;

        $this->assertFalse($viewClass->hasItem('foo'));
        $viewClass->setItem('foo', 'bar');
        $this->assertTrue($viewClass->hasItem('foo'));
        $this->assertEquals('bar', $viewClass->getItem('foo'));
        $this->assertEquals(['foo' => 'bar'], $viewClass->getItems());
    }

    /**
     * @test
     */
    public function setItemOnFrozenConfigurationIsChecked()
    {
        $viewClass = $this->getViewClassWithConfigurationFreezeStateCheck();

        $viewClass->setItem('foo', 'bar');
    }

    /**
     * @test
     */
    public function itemNotFoundThrowsException()
    {
        $this->setExpectedException(EntryNotFoundException::class);

        $viewClass = new ViewClass;
        $viewClass->getItem('nope');
    }

    /**
     * @test
     */
    public function configurationObjectDataPreProcessed()
    {
        $preProcessor = new DataPreProcessor;
        $preProcessor->setData(['foo' => 'bar']);

        ViewClass::dataPreProcessor($preProcessor);

        $this->assertEquals(
            ['items' => ['foo' => 'bar']],
            $preProcessor->getData()
        );
    }

    /**
     * @return ViewClass|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getViewClassWithConfigurationFreezeStateCheck()
    {
        /** @var ViewClass|\PHPUnit_Framework_MockObject_MockObject $viewClass */
        $viewClass = $this->getMockBuilder(ViewClass::class)
            ->setMethods(['checkConfigurationFreezeState'])
            ->getMock();

        $viewClass->expects($this->once())
            ->method('checkConfigurationFreezeState');

        return $viewClass;
    }
}
