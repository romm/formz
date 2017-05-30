<?php

namespace Romm\Formz\Tests\Unit\Form\FormObject\Service;

use Romm\Formz\Form\FormObject\Definition\FormDefinitionObject;
use Romm\Formz\Form\FormObject\FormObjectStatic;
use Romm\Formz\Form\FormObject\Service\FormObjectProperties;
use Romm\Formz\Tests\Fixture\Form\ExtendedForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class FormObjectPropertiesTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function propertiesCanBeFetched()
    {
        $service = new FormObjectProperties($this->getStatic());

        $this->assertSame(
            ['bar', 'publicProperty', 'foo'],
            $service->getProperties()
        );
    }

    /**
     * @return FormObjectStatic
     */
    protected function getStatic()
    {
        /** @var FormDefinitionObject $formDefinitionObject */
        $formDefinitionObject = $this->prophesize(FormDefinitionObject::class)->reveal();

        return new FormObjectStatic(ExtendedForm::class, $formDefinitionObject);
    }
}
