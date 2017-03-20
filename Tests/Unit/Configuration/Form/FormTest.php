<?php
namespace Romm\Formz\Tests\Unit\Configuration;

use Romm\Formz\Condition\Items\ConditionItemInterface;
use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Configuration\Form\Form;
use Romm\Formz\Configuration\Form\Settings\FormSettings;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class FormTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function initializationDoneProperly()
    {
        $form = new Form;
        $this->assertInstanceOf(FormSettings::class, $form->getSettings());
    }

    /**
     * @test
     */
    public function rootConfigurationIsFetched()
    {
        $form = new Form;
        $rootConfiguration = new Configuration;

        $form->setParents([$rootConfiguration]);

        $this->assertSame($rootConfiguration, $form->getRootConfiguration());
    }

    /**
     * @test
     */
    public function addFieldAddsField()
    {
        $form = new Form;
        $field = new Field;
        $field->setName('foo');

        $this->assertFalse($form->hasField('foo'));
        $form->addField($field);
        $this->assertTrue($form->hasField('foo'));
        $this->assertSame($field, $form->getField('foo'));
        $this->assertSame(['foo' => $field], $form->getFields());
    }

    /**
     * @test
     */
    public function getUnknownFieldThrowsException()
    {
        $this->setExpectedException(EntryNotFoundException::class);

        $form = new Form;
        $form->getField('nope');
    }

    /**
     * @test
     */
    public function addConditionAddsCondition()
    {
        $form = new Form;
        $condition = $this->getMockBuilder(ConditionItemInterface::class)
            ->getMockForAbstractClass();

        $this->assertEmpty($form->getConditionList());

        $form->addCondition('foo', $condition);
        $this->assertEquals(
            ['foo' => $condition],
            $form->getConditionList()
        );
    }
}
