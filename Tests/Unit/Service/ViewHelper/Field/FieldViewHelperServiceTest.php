<?php
namespace Romm\Formz\Tests\Unit\Service\ViewHelper\Field;

use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Configuration\View\Layouts\Layout;
use Romm\Formz\Service\ViewHelper\Field\FieldViewHelperService;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class FieldViewHelperServiceTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function setCurrentFieldSetsCurrentField()
    {
        $fieldService = new FieldViewHelperService;
        $field = new Field;

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
        $field = new Field;

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
        $field1 = new Field;
        $field2 = new Field;

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
        $fieldService = new FieldViewHelperService;

        $layout1 = new Layout;
        $layout1->setTemplateFile('foo/bar');

        $layout2 = new Layout;
        $layout2->setTemplateFile('bar/baz');

        $view1 = $fieldService->getView($layout1);
        $view2 = $fieldService->getView($layout1);

        $this->assertSame($view1, $view2);

        $view3 = $fieldService->getView($layout2);

        $this->assertNotSame($view1, $view3);
    }
}
