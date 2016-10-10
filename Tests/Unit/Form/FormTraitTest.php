<?php
namespace Romm\Formz\Tests\Unit\Form;

use Romm\Formz\Form\FormTrait;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class FormTraitTest extends AbstractUnitTest
{

    /**
     * Checks that the validation data of a form can be set/get correctly.
     *
     * @test
     */
    public function validationDataCanBeGet()
    {
        /** @var FormTrait $formTraitMock */
        $formTraitMock = $this->getMockForTrait(FormTrait::class);

        $data = [
            'foo' => 'foo',
            'bar' => 'bar'
        ];

        $formTraitMock->setValidationData($data);

        $this->assertEquals($data, $formTraitMock->getValidationData());
        $this->assertEquals($data['foo'], $formTraitMock->getValidationData('foo'));
        $this->assertEquals($data['bar'], $formTraitMock->getValidationData('bar'));
    }
}
