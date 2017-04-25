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
     * Checks that the configuration validation result is correctly built and
     * can be fetched.
     *
     * @test
     */
    public function configurationValidationResultCanBeGet()
    {
        $formObject = $this->getDefaultFormObject();
        $formObject->addProperty('foo');

        $validationResult = $formObject->getDefinitionValidationResult();
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

        $formResult = new FormResult;

        $this->assertFalse($formObject->hasFormResult());
        $formObject->setFormResult($formResult);
        $this->assertTrue($formObject->hasFormResult());
        $this->assertSame($formResult, $formObject->getFormResult());
    }
}
