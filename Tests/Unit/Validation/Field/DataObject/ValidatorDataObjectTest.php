<?php
namespace Romm\Formz\Tests\Unit\Validation\Field\DataObject;

use Romm\Formz\Form\Definition\Field\Validation\Validator;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Tests\Fixture\Validation\Validator\DummyFieldValidator;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Validation\Field\DataObject\ValidatorDataObject;

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

        $validation = new Validator('foo', DummyFieldValidator::class);

        $validatorDataObject = new ValidatorDataObject($formObject, $validation);

        $this->assertSame($formObject, $validatorDataObject->getFormObject());
        $this->assertSame($validation, $validatorDataObject->getValidator());
    }
}
