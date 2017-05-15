<?php

namespace Romm\Formz\Tests\Unit\Form\FormObject;

use Romm\Formz\Form\Definition\FormDefinition;
use Romm\Formz\Form\FormObject\Definition\FormDefinitionObject;
use Romm\Formz\Form\FormObject\FormObjectStatic;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Extbase\Error\Result;

class FormObjectStaticTest extends AbstractUnitTest
{
    /**
     * The class name given in the constructor can be retrieved with its getter
     * function.
     *
     * @test
     */
    public function classNameCanBeRetrieved()
    {
        $className = DefaultForm::class;

        $static = new FormObjectStatic($className, $this->getEmptyFormDefinitionObject());

        $this->assertSame($className, $static->getClassName());
    }

    /**
     * The form definition given in the constructor can be retrieved with its
     * getter function.
     *
     * @test
     */
    public function definitionCanBeRetrieved()
    {
        $formDefinition = $this->getMockBuilder(FormDefinition::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var FormDefinitionObject|\PHPUnit_Framework_MockObject_MockObject $formDefinitionObject */
        $formDefinitionObject = $this->getMockBuilder(FormDefinitionObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefinition'])
            ->getMock();

        $formDefinitionObject->expects($this->once())
            ->method('getDefinition')
            ->willReturn($formDefinition);

        $static = new FormObjectStatic(DefaultForm::class, $formDefinitionObject);

        $this->assertSame($formDefinition, $static->getDefinition());
    }

    /**
     * The object hash should be calculated once and use memoization.
     *
     * @test
     */
    public function objectHashIsCalculatedOnce()
    {
        $objectHash = 'foo-bar-baz';

        /** @var FormObjectStatic|\PHPUnit_Framework_MockObject_MockObject $static */
        $static = $this->getMockBuilder(FormObjectStatic::class)
            ->disableOriginalConstructor()
            ->setMethods(['calculateObjectHash'])
            ->getMock();

        $static->expects($this->once())
            ->method('calculateObjectHash')
            ->willReturn($objectHash);

        $this->assertSame($objectHash, $static->getObjectHash());
        $this->assertSame($objectHash, $static->getObjectHash());
    }

    /**
     * The object hash must be the same for two static instances with the exact
     * same data (and different if anything changes from one to another).
     *
     * @test
     */
    public function objectHashIsTheSameForIdenticalStatic()
    {
        $formDefinition1 = new FormDefinition;
        $formDefinition2 = new FormDefinition;
        $formDefinition2->addField('foo');

        $formDefinitionObject1 = new FormDefinitionObject($formDefinition1, new Result);
        $formDefinitionObject2 = new FormDefinitionObject($formDefinition2, new Result);

        $static1 = new FormObjectStatic(DefaultForm::class, $formDefinitionObject1);
        $static2 = new FormObjectStatic(DefaultForm::class, $formDefinitionObject1);
        $static3 = new FormObjectStatic(DefaultForm::class, $formDefinitionObject2);

        $hash1 = $static1->getObjectHash();
        $hash2 = $static2->getObjectHash();
        $hash3 = $static3->getObjectHash();

        $this->assertSame($hash1, $hash2);
        $this->assertNotSame($hash1, $hash3);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FormDefinitionObject
     */
    protected function getEmptyFormDefinitionObject()
    {
        return $this->getMockBuilder(FormDefinitionObject::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
