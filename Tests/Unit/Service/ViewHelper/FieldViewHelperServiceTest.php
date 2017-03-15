<?php
namespace Romm\Formz\Tests\Unit\Service\ViewHelper;

use Romm\Formz\Configuration\Form\Field\Field;
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
    public function viewIsInstantiatedOnce()
    {
        $fieldService = new FieldViewHelperService;
        $view1 = $fieldService->getView();
        $view2 = $fieldService->getView();

        $this->assertSame($view1, $view2);
    }
}
