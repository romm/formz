<?php
namespace Romm\Formz\Tests\Unit\Service\ViewHelper\Field;

use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Service\ViewHelper\Field\FieldContextEntry;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class FieldContentEntryTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function initializationDoneProperly()
    {
        $field = new Field;

        $fieldContextEntry = new FieldContextEntry($field);
        $this->assertSame($field, $fieldContextEntry->getField());
    }

    /**
     * @test
     */
    public function setFieldOptionSetsFieldOption()
    {
        $fieldContextEntry = new FieldContextEntry(new Field);

        $fieldContextEntry->setOption('foo', 'bar');
        $this->assertEquals(
            ['foo' => 'bar'],
            $fieldContextEntry->getOptions()
        );

        $fieldContextEntry->setOption('bar', 'baz');
        $this->assertEquals(
            [
                'foo' => 'bar',
                'bar' => 'baz'
            ],
            $fieldContextEntry->getOptions()
        );

        $fieldContextEntry->setOption('foo', 'baz');
        $this->assertEquals(
            [
                'foo' => 'baz',
                'bar' => 'baz'
            ],
            $fieldContextEntry->getOptions()
        );
    }
}
