<?php
namespace Romm\Formz\Tests\Unit\Service\ViewHelper\Field;

use Romm\Formz\Configuration\View\Layouts\Layout;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Service\ViewHelper\Field\FieldViewHelperService;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Fluid\View\StandaloneView;

class FieldViewHelperServiceTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function setCurrentFieldSetsCurrentField()
    {
        $fieldService = new FieldViewHelperService;
        $field = new Field('foo');

        $this->assertFalse($fieldService->fieldContextExists());
        $fieldService->setCurrentField($field);
        $this->assertTrue($fieldService->fieldContextExists());
        $this->assertSame($field, $fieldService->getCurrentField());
    }

    /**
     * @test
     */
    public function resetStateResetsState()
    {
        $fieldService = new FieldViewHelperService;
        $field = new Field('foo');

        $this->assertFalse($fieldService->fieldContextExists());
        $fieldService->setCurrentField($field);
        $this->assertTrue($fieldService->fieldContextExists());

        $fieldService->removeCurrentField();

        $this->assertFalse($fieldService->fieldContextExists());
    }

    /**
     * @test
     */
    public function nestingFieldsWorks()
    {
        $fieldService = new FieldViewHelperService;
        $field1 = new Field('foo');
        $field2 = new Field('foo');

        $fieldService->setCurrentField($field1);
        $this->assertSame($field1, $fieldService->getCurrentField());
        $fieldService->setCurrentField($field2);
        $this->assertSame($field2, $fieldService->getCurrentField());
        $fieldService->removeCurrentField();
        $this->assertSame($field1, $fieldService->getCurrentField());
    }

    /**
     * @test
     */
    public function viewIsInstantiatedOncePerLayout()
    {
        /** @var FieldViewHelperService|\PHPUnit_Framework_MockObject_MockObject $fieldService */
        $fieldService = $this->getMockBuilder(FieldViewHelperService::class)
            ->setMethods(['getViewInstance'])
            ->getMock();

        $layout1 = new Layout;
        $layout1->setTemplateFile('foo/bar');

        $layout2 = new Layout;
        $layout2->setTemplateFile('bar/baz');

        $fieldService->expects($this->exactly(2))
            ->method('getViewInstance')
            ->withConsecutive($layout1, $layout2)
            ->willReturnCallback(function () {
                return $this->prophesize(StandaloneView::class)->reveal();
            });

        $fieldService->getView($layout1);
        $fieldService->getView($layout1);
        $fieldService->getView($layout2);
        $fieldService->getView($layout2);
    }
}
