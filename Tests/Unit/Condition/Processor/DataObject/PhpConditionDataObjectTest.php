<?php
namespace Romm\Formz\Tests\Unit\Condition\Processor\DataObject;

use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Validation\Form\FormValidatorExecutor;

class PhpConditionDataObjectTest extends AbstractUnitTest
{
    public function constructArgumentsInjectValues()
    {
        /** @var FormInterface $form */
        $form = $this->getMockBuilder(FormInterface::class)
            ->getMock();

        /** @var FormValidatorExecutor $formValidator */
        $formValidator = $this->getMockBuilder(FormValidatorExecutor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $object = $this->getDataObject($form, $formValidator);

        $this->assertSame($form, $object->getForm());
        $this->assertSame($formValidator, $object->getFormValidator());
    }

    /**
     * @test
     */
    public function setFormSetsForm()
    {
        $object = $this->getDataObject();

        /** @var FormInterface $form */
        $form = $this->getMockBuilder(FormInterface::class)
            ->getMock();

        $object->setForm($form);
        $this->assertSame($form, $object->getForm());
    }

    /**
     * @test
     */
    public function setFormValidatorSetsFormValidator()
    {
        $object = $this->getDataObject();

        /** @var FormValidatorExecutor $formValidatorExecutor */
        $formValidatorExecutor = $this->getMockBuilder(FormValidatorExecutor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $object->setFormValidator($formValidatorExecutor);
        $this->assertSame($formValidatorExecutor, $object->getFormValidator());
    }

    /**
     * @param FormInterface         $form
     * @param FormValidatorExecutor $formValidatorExecutor
     * @return PhpConditionDataObject
     */
    protected function getDataObject(FormInterface $form = null, FormValidatorExecutor $formValidatorExecutor = null)
    {
        /** @var FormInterface $form */
        $form = $form ?: $this->getMockBuilder(FormInterface::class)
            ->getMock();

        /** @var FormValidatorExecutor $formValidatorExecutor */
        $formValidatorExecutor = $formValidatorExecutor ?: $this->getMockBuilder(FormValidatorExecutor::class)
            ->disableOriginalConstructor()
            ->getMock();

        return new PhpConditionDataObject($form, $formValidatorExecutor);
    }
}
