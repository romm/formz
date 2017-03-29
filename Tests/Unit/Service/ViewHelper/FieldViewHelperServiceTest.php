<?php
namespace Romm\Formz\Tests\Unit\Service\ViewHelper;

use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Configuration\View\Layouts\Layout;
use Romm\Formz\Service\ViewHelper\FieldViewHelperService;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class FieldViewHelperServiceTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function formContextActivatedTwiceThrowsException()
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
    public function setFieldOptionSetsFieldOption()
    {
        $fieldService = new FieldViewHelperService;

        $fieldService->setFieldOption('foo', 'bar');
        $this->assertEquals(
            ['foo' => 'bar'],
            $fieldService->getFieldOptions()
        );

        $fieldService->setFieldOption('bar', 'baz');
        $this->assertEquals(
            [
                'foo' => 'bar',
                'bar' => 'baz'
            ],
            $fieldService->getFieldOptions()
        );

        $fieldService->setFieldOption('foo', 'baz');
        $this->assertEquals(
            [
                'foo' => 'baz',
                'bar' => 'baz'
            ],
            $fieldService->getFieldOptions()
        );
    }

    /**
     * @test
     */
    public function resetStateResetsState()
    {
        $fieldService = new FieldViewHelperService;
        $field = new Field;

        $fieldService->setCurrentField($field);
        $fieldService->setFieldOption('foo', 'bar');

        $fieldService->resetState();

        $this->assertFalse($fieldService->fieldContextExists());
        $this->assertNull($fieldService->getCurrentField());
        $this->assertEmpty($fieldService->getFieldOptions());
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
