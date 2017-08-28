<?php
namespace Romm\Formz\Tests\Unit\Condition\Items;

use Romm\Formz\Condition\Items\FieldIsEmptyCondition;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Validation\Form\FormValidatorExecutor;

class FieldIsEmptyConditionTest extends AbstractConditionItemUnitTest
{
    /**
     * @test
     */
    public function wrongFieldNameThrowsException()
    {
        /** @var FieldIsEmptyCondition $conditionItem */
        $conditionItem = $this->getConditionItemWithFailedConfigurationValidation(
            FieldIsEmptyCondition::class,
            ['fieldName' => 'baz'],
            1488191994
        );
        $conditionItem->validateConditionConfiguration($this->getDefaultFormObject()->getDefinition());
    }

    /**
     * @test
     */
    public function validConfiguration()
    {
        /** @var FieldIsEmptyCondition $conditionItem */
        $conditionItem = $this->getConditionItemWithValidConfigurationValidation(
            FieldIsEmptyCondition::class,
            ['fieldName' => 'foo']
        );
        $conditionItem->validateConditionConfiguration($this->getDefaultFormObject()->getDefinition());
    }

    /**
     * The field is not empty.
     *
     * @test
     */
    public function phpConditionIsNotVerifiedWithFilledFieldValue()
    {
        $conditionItem = new FieldIsEmptyCondition('foo');

        $formObject = $this->getDefaultFormObject();
        $conditionItem->attachFormObject($formObject);

        /** @var FormValidatorExecutor $formValidator */
        $formValidator = $this->getMockBuilder(FormValidatorExecutor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $form = new DefaultForm;
        $form->setFoo('bar');

        $phpConditionDataObject = new PhpConditionDataObject($form, $formValidator);

        $result = $conditionItem->getPhpResult($phpConditionDataObject);

        $this->assertFalse($result);
    }

    /**
     * The field is empty.
     *
     * @test
     */
    public function phpConditionIsVerifiedWithGivenFieldValue()
    {
        $conditionItem = new FieldIsEmptyCondition('foo');

        $formObject = $this->getDefaultFormObject();
        $conditionItem->attachFormObject($formObject);

        /** @var FormValidatorExecutor $formValidator */
        $formValidator = $this->getMockBuilder(FormValidatorExecutor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $form = new DefaultForm;
        $form->setFoo('');

        $phpConditionDataObject = new PhpConditionDataObject($form, $formValidator);

        $result = $conditionItem->getPhpResult($phpConditionDataObject);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function getCssResultEmpty()
    {
        $conditionItem = new FieldIsEmptyCondition('foo');

        $this->assertEquals('[fz-value-foo=""]', $conditionItem->getCssResult());
    }

    /**
     * @test
     */
    public function getJavaScriptResult()
    {
        $assert = 'Fz.Condition.validateCondition(\'Romm\\\\Formz\\\\Condition\\\\Items\\\\FieldIsEmptyCondition\', form, {"fieldName":"foo"})';

        $conditionItem = new FieldIsEmptyCondition('foo');

        $this->assertEquals($assert, $conditionItem->getJavaScriptResult());
    }
}
