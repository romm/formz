<?php
namespace Romm\Formz\Tests\Unit\Validation\Validator\DataObject;

use Romm\Formz\Form\Definition\Field\Validation\Validation;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Validation\DataObject\ValidatorDataObject;

class ValidatorDataObjectTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function constructorPropertiesAreGettable()
    {
        /** @var FormObject $formObject */
        $formObject = $this->getMockBuilder(FormObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Validation $validation */
        $validation = $this->getMockBuilder(Validation::class)
            ->getMock();

        $validatorDataObject = new ValidatorDataObject($formObject, $validation);

        $this->assertSame($formObject, $validatorDataObject->getFormObject());
        $this->assertSame($validation, $validatorDataObject->getValidation());
    }
}
