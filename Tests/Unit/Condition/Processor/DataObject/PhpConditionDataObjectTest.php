<?php
namespace Romm\Formz\Tests\Unit\Condition\Processor\DataObject;

use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Validation\Validator\Form\AbstractFormValidator;

class PhpConditionDataObjectTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function setFormSetsForm()
    {
        $object = new PhpConditionDataObject;
        $form = $this->getMockBuilder(FormInterface::class)
            ->getMock();

        $object->setForm($form);
        $this->assertSame(
            $form,
            $object->getForm()
        );
    }

    /**
     * @test
     */
    public function setFormValidatorSetsFormValidator()
    {
        $object = new PhpConditionDataObject;
        $formValidator = $this->getMockBuilder(AbstractFormValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $object->setFormValidator($formValidator);
        $this->assertSame(
            $formValidator,
            $object->getFormValidator()
        );
    }
}
