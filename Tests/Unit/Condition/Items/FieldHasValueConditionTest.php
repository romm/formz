<?php
namespace Romm\Formz\Tests\Unit\Condition\Items;

use Romm\Formz\Condition\Items\FieldHasValueCondition;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Validation\Validator\Form\FormValidatorExecutor;

class FieldHasValueConditionTest extends AbstractConditionItemUnitTest
{
    /**
     * @test
     */
    public function wrongFieldNameThrowsException()
    {
        /** @var FieldHasValueCondition $conditionItem */
        $conditionItem = $this->getConditionItemWithFailedConfigurationValidation(FieldHasValueCondition::class, 1488192031);
        $conditionItem->setFieldName('baz');
        $conditionItem->validateConditionConfiguration($this->getDefaultFormObject()->getDefinition());
    }

    /**
     * @test
     */
    public function validConfiguration()
    {
        /** @var FieldHasValueCondition $conditionItem */
        $conditionItem = $this->getConditionItemWithValidConfigurationValidation(FieldHasValueCondition::class);
        $conditionItem->setFieldName('foo');
        $conditionItem->validateConditionConfiguration($this->getDefaultFormObject()->getDefinition());
    }

    /**
     * The field does not have the given value.
     *
     * @test
     */
    public function phpConditionIsNotVerifiedWithGivenFieldValue()
    {
        $conditionItem = new FieldHasValueCondition;
        $conditionItem->setFieldName('foo');
        $conditionItem->setFieldValue('nope');

        $formObject = $this->getDefaultFormObject();
        $conditionItem->attachFormObject($formObject);

        /** @var FormValidatorExecutor $formValidator */
        $formValidator = $this->getMockBuilder(FormValidatorExecutor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $form = new DefaultForm;
        $form->setFoo('yup');

        $phpConditionDataObject = new PhpConditionDataObject($form, $formValidator);

        $result = $conditionItem->getPhpResult($phpConditionDataObject);

        $this->assertFalse($result);
    }

    /**
     * The field does have the given value.
     *
     * @test
     */
    public function phpConditionIsVerifiedWithGivenFieldValue()
    {
        $conditionItem = new FieldHasValueCondition;
        $conditionItem->setFieldName('foo');
        $conditionItem->setFieldValue('yup');

        $formObject = $this->getDefaultFormObject();
        $conditionItem->attachFormObject($formObject);

        /** @var FormValidatorExecutor $formValidator */
        $formValidator = $this->getMockBuilder(FormValidatorExecutor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $form = new DefaultForm;
        $form->setFoo('yup');

        $phpConditionDataObject = new PhpConditionDataObject($form, $formValidator);

        $result = $conditionItem->getPhpResult($phpConditionDataObject);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function getCssResult()
    {
        $conditionItem = new FieldHasValueCondition;
        $conditionItem->setFieldName('foo');
        $conditionItem->setFieldValue('bar');

        $this->assertEquals('[fz-value-foo~="bar"]', $conditionItem->getCssResult());
    }

    /**
     * @test
     */
    public function getCssResultEmpty()
    {
        $conditionItem = new FieldHasValueCondition;
        $conditionItem->setFieldName('foo');

        $this->assertEquals('[fz-value-foo=""]', $conditionItem->getCssResult());
    }

    /**
     * @test
     */
    public function getJavaScriptResult()
    {
        $assert = 'Fz.Condition.validateCondition(\'Romm\\\\Formz\\\\Condition\\\\Items\\\\FieldHasValueCondition\', form, {"fieldName":"foo","fieldValue":"bar"})';

        $conditionItem = new FieldHasValueCondition;
        $conditionItem->setFieldName('foo');
        $conditionItem->setFieldValue('bar');

        $this->assertEquals($assert, $conditionItem->getJavaScriptResult());
    }
}
