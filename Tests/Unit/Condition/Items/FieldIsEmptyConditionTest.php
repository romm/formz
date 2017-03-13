<?php
namespace Romm\Formz\Tests\Unit\Condition\Items;

use Romm\Formz\Condition\Items\FieldIsEmptyCondition;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Validation\Validator\Form\FormValidatorExecutor;

class FieldIsEmptyConditionTest extends AbstractConditionItemUnitTest
{
    /**
     * @test
     */
    public function wrongFieldNameThrowsException()
    {
        /** @var FieldIsEmptyCondition $conditionItem */
        $conditionItem = $this->getConditionItemWithFailedConfigurationValidation(FieldIsEmptyCondition::class, 1488191994);
        $conditionItem->setFieldName('baz');
        $conditionItem->validateConditionConfiguration();
    }

    /**
     * @test
     */
    public function validConfiguration()
    {
        /** @var FieldIsEmptyCondition $conditionItem */
        $conditionItem = $this->getConditionItemWithValidConfigurationValidation(FieldIsEmptyCondition::class);
        $conditionItem->setFieldName('foo');
        $conditionItem->validateConditionConfiguration();
    }

    /**
     * The field is not empty.
     *
     * @test
     */
    public function phpConditionIsNotVerifiedWithFilledFieldValue()
    {
        $conditionItem = new FieldIsEmptyCondition;
        $conditionItem->setFieldName('foo');

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
        $conditionItem = new FieldIsEmptyCondition;
        $conditionItem->setFieldName('foo');

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
        $conditionItem = new FieldIsEmptyCondition;
        $conditionItem->setFieldName('foo');

        $this->assertEquals('[formz-value-foo=""]', $conditionItem->getCssResult());
    }

    /**
     * @test
     */
    public function getJavaScriptResult()
    {
        $assert = 'Fz.Condition.validateCondition(\'Romm\\\\Formz\\\\Condition\\\\Items\\\\FieldIsEmptyCondition\', form, {"fieldName":"foo"})';

        $conditionItem = new FieldIsEmptyCondition;
        $conditionItem->setFieldName('foo');

        $this->assertEquals($assert, $conditionItem->getJavaScriptResult());
    }
}
