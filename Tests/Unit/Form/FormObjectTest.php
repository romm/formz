<?php
namespace Romm\Formz\Tests\Unit\Form;

use Romm\Formz\Error\FormResult;
use Romm\Formz\Form\FormObject;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Extbase\Error\Result;

class FormObjectTest extends AbstractUnitTest
{

    /**
     * Checking that getter functions for constructor parameters work well.
     *
     * @test
     */
    public function constructorPropertiesAreSet()
    {
        $formObject = $this->getDefaultFormObject();

        $this->assertEquals(self::FORM_OBJECT_DEFAULT_CLASS_NAME, $formObject->getClassName());
        $this->assertEquals(self::FORM_OBJECT_DEFAULT_NAME, $formObject->getName());

        unset($formObject);
    }

    /**
     * @test
     */
    public function addedPropertyIsGettable()
    {
        $formObject = $this->getDefaultFormObject();

        $formObject->addProperty('foo');
        $this->assertEquals(['foo'], $formObject->getProperties());

        $formObject->addProperty('bar');
        $this->assertEquals(['foo', 'bar'], $formObject->getProperties());

        // Trying to add several times the same property should not insert duplicates.
        $formObject->addProperty('foo');
        $this->assertEquals(['foo', 'bar'], $formObject->getProperties());

        unset($formObject);
    }

    /**
     * @test
     */
    public function formObjectWithAddedPropertyHasProperty()
    {
        $formObject = $this->createFormObject();

        $this->assertFalse($formObject->hasProperty('foo'));
        $this->assertFalse($formObject->hasProperty('bar'));
        $formObject->addProperty('foo');
        $this->assertTrue($formObject->hasProperty('foo'));
        $this->assertFalse($formObject->hasProperty('bar'));
        $formObject->addProperty('bar');
        $this->assertTrue($formObject->hasProperty('foo'));
        $this->assertTrue($formObject->hasProperty('bar'));
    }

    /**
     * Checking that the hash can be retrieved with its getter.
     *
     * @test
     */
    public function hashCanBeRetrieved()
    {
        /** @var FormObject|\PHPUnit_Framework_MockObject_MockObject $formObject */
        $formObject = $this->getMockBuilder(FormObject::class)
            ->setMethods(['calculateHash'])
            ->setConstructorArgs([self::FORM_OBJECT_DEFAULT_CLASS_NAME, self::FORM_OBJECT_DEFAULT_NAME, []])
            ->getMock();
        $hash = 'foo';

        $formObject->expects($this->any())
            ->method('calculateHash')
            ->willReturn($hash);

        $this->assertEquals($hash, $formObject->getHash());

        unset($formObject);
    }

    /**
     * The hash should be calculated only once, as it can lead to performance
     * issues if the object is used many times.
     *
     * @test
     */
    public function hashIsCalculatedOnlyOnce()
    {
        /** @var FormObject|\PHPUnit_Framework_MockObject_MockObject $formObject */
        $formObject = $this->getMockBuilder(FormObject::class)
            ->setMethods(['calculateHash'])
            ->setConstructorArgs([self::FORM_OBJECT_DEFAULT_CLASS_NAME, self::FORM_OBJECT_DEFAULT_NAME, []])
            ->getMock();

        $formObject->expects($this->once())
            ->method('calculateHash')
            ->willReturn('foo');

        for ($i = 0; $i < 3; $i++) {
            $formObject->getHash();
        }

        unset($formObject);
    }

    /**
     * Checks that the configuration validation result is correctly built and
     * can be fetched.
     *
     * @test
     */
    public function configurationValidationResultCanBeGet()
    {
        $formObject = $this->getDefaultFormObject();
        $formObject->addProperty('foo');

        $validationResult = $formObject->getConfigurationValidationResult();
        $this->assertInstanceOf(Result::class, $validationResult);
        $this->assertFalse($validationResult->hasErrors());

        unset($formObject);
    }

    /**
     * @test
     */
    public function setLastValidationResultSetsLastValidationResult()
    {
        $formObject = new FormObject('foo', 'foo', []);

        $validationResult = new FormResult;

        $this->assertFalse($formObject->hasLastValidationResult());
        $formObject->setLastValidationResult($validationResult);
        $this->assertTrue($formObject->hasLastValidationResult());
        $this->assertSame($validationResult, $formObject->getLastValidationResult());
    }
}
