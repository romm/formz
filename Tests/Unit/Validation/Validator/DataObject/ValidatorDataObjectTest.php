<?php
namespace Romm\Formz\Tests\Unit\Validation\Validator\DataObject;

use Romm\Formz\Configuration\Form\Field\Validation\Validation;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObject;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Validation\DataObject\ValidatorDataObject;

class ValidatorDataObjectTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function constructorPropertiesAreGettable()
    {
        $formObject = $this->getMockBuilder(FormObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $form = $this->getMockBuilder(FormInterface::class)
            ->getMock();
        $validation = $this->getMockBuilder(Validation::class)
            ->getMock();

        $validatorDataObject = new ValidatorDataObject($formObject, $form, $validation);

        $this->assertSame($formObject, $validatorDataObject->getFormObject());
        $this->assertSame($form, $validatorDataObject->getForm());
        $this->assertSame($validation, $validatorDataObject->getValidation());
    }
}
