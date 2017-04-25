<?php
namespace Romm\Formz\Tests\Unit\Form\Definition;

use Romm\Formz\Condition\Items\ConditionItemInterface;
use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\Definition\FormDefinition;
use Romm\Formz\Form\Definition\Settings\FormSettings;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class FormDefinitionTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function initializationDoneProperly()
    {
        $form = new FormDefinition;
        $this->assertInstanceOf(FormSettings::class, $form->getSettings());
    }

    /**
     * @test
     */
    public function rootConfigurationIsFetched()
    {
        $form = new FormDefinition;
        $rootConfiguration = new Configuration;

        $form->setParents([$rootConfiguration]);

        $this->assertSame($rootConfiguration, $form->getRootConfiguration());
    }

    /**
     * @test
     */
    public function addFieldAddsField()
    {
        $form = new FormDefinition;
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

        $form = new FormDefinition;
        $form->getField('nope');
    }

    /**
     * @test
     */
    public function addConditionAddsCondition()
    {
        $form = new FormDefinition;
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
