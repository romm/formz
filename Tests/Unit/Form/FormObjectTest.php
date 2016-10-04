<?php
namespace Romm\Formz\Tests\Unit\Form;

use Romm\ConfigurationObject\ConfigurationObjectInstance;
use Romm\ConfigurationObject\Tests\Unit\ConfigurationObjectUnitTestUtility;
use Romm\Formz\Configuration\Form\Form;
use Romm\Formz\Form\FormObject;
use Romm\Formz\Tests\Unit\FormzUnitTestUtility;
use TYPO3\CMS\Core\Tests\UnitTestCase;

class FormObjectTest extends UnitTestCase
{

    use ConfigurationObjectUnitTestUtility;
    use FormzUnitTestUtility;

    const FORM_OBJECT_DEFAULT_CLASS_NAME = \stdClass::class;
    const FORM_OBJECT_DEFAULT_NAME = 'foo';

    protected function setUp()
    {
        $this->initializeConfigurationObjectTestServices();
        $this->injectMockedConfigurationServicesUtility();
        $this->injectTransientMemoryCacheInCore();
    }

    /**
     * Checking that getter functions for constructor parameters work well.
     *
     * @test
     */
    public function constructorPropertiesAreSet()
    {
        $formObject = $this->getFormObject();

        $this->assertEquals(self::FORM_OBJECT_DEFAULT_CLASS_NAME, $formObject->getClassName());
        $this->assertEquals(self::FORM_OBJECT_DEFAULT_NAME, $formObject->getName());

        unset($formObject);
    }

    /**
     * @test
     */
    public function addedPropertyIsGettable()
    {
        $formObject = $this->getFormObject();

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
     * Setting a basic configuration array should be saved properly.
     *
     * @test
     */
    public function configurationArrayCanBeSet()
    {
        $formObject = $this->getFormObject();
        $arrayConfiguration = [
            'fields' => [
                'foo' => 'foo'
            ],
            'bar'    => 'bar'
        ];

        $formObject->addProperty('foo');
        $formObject->setConfigurationArray($arrayConfiguration);

        $this->assertEquals($arrayConfiguration, $formObject->getConfigurationArray());

        unset($formObject);
    }

    /**
     * When injecting an array configuration, the property `fields` of the array
     * should contain only fields that were added with the function
     * `addProperty()`.
     *
     * The function will sanitize the array by removing the fields not found in
     * the properties list.
     *
     * @test
     */
    public function configurationArrayDeletesAdditionalFields()
    {
        $formObject = $this->getFormObject();
        $arrayConfiguration = [
            'fields' => [
                'foo' => 'foo'
            ],
            'bar'    => 'bar'
        ];
        $additionalFieldsArrayConfiguration = [
            'fields' => [
                'bar' => 'bar'
            ]
        ];

        $formObject->addProperty('foo');
        $formObject->setConfigurationArray(array_merge_recursive($arrayConfiguration, $additionalFieldsArrayConfiguration));

        $this->assertEquals($arrayConfiguration, $formObject->getConfigurationArray());

        unset($formObject);
    }

    /**
     * Checking that the hash can be retrieved with its getter.
     *
     * @test
     */
    public function hashCanBeRetrieved()
    {
        /** @var FormObject|\PHPUnit_Framework_MockObject_MockObject $formObject */
        $formObject = $this->getMock(FormObject::class, ['calculateHash'], [self::FORM_OBJECT_DEFAULT_CLASS_NAME, self::FORM_OBJECT_DEFAULT_NAME]);
        $hash = 'foo';

        $formObject->expects($this->any())
            ->method('calculateHash')
            ->willReturn($hash);

        $this->assertEquals($hash, $formObject->getHash());

        unset($formObject);
    }

    /**
     * The hash should be calculated only once, as it is
     *
     * @test
     */
    public function hashIsCalculatedOnlyOnce()
    {
        /** @var FormObject|\PHPUnit_Framework_MockObject_MockObject $formObject */
        $formObject = $this->getMock(FormObject::class, ['calculateHash'], [self::FORM_OBJECT_DEFAULT_CLASS_NAME, self::FORM_OBJECT_DEFAULT_NAME]);

        $formObject->expects($this->once())
            ->method('calculateHash')
            ->willReturn('foo');

        for ($i = 0; $i < 3; $i++) {
            $formObject->getHash();
        }

        unset($formObject);
    }

    /**
     * Will inject a basic configuration array, and check that the configuration
     * object created by the form object is valid and without errors.
     *
     * @test
     */
    public function configurationObjectIsCorrectlyBuilt()
    {
        $formObject = $this->getFormObject();
        $arrayConfiguration = [
            'fields' => [
                'foo' => []
            ],
        ];

        $formObject->addProperty('foo');
        $formObject->setConfigurationArray($arrayConfiguration);

        $configurationObject = $formObject->getConfigurationObject();

        $this->assertEquals(ConfigurationObjectInstance::class, get_class($configurationObject));
        $this->assertEquals(Form::class, get_class($configurationObject->getObject(true)));
        $this->assertFalse($configurationObject->getValidationResult()->hasErrors());
        $this->assertSame($configurationObject->getObject(true), $formObject->getConfiguration());

        unset($formObject);
    }

    /**
     * @return FormObject
     */
    protected function getFormObject()
    {
        return new FormObject(self::FORM_OBJECT_DEFAULT_CLASS_NAME, self::FORM_OBJECT_DEFAULT_NAME);
    }
}
