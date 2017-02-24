<?php
namespace Romm\Formz\Tests\Unit\Form;

use Romm\ConfigurationObject\ConfigurationObjectInstance;
use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Configuration\Form\Form;
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
        $formObject = $this->getDefaultFormObject();

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
     * Setting a basic configuration array should be saved properly.
     *
     * @test
     */
    public function configurationArrayCanBeSet()
    {
        $formObject = $this->getDefaultFormObject();
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
        $formObject = $this->getDefaultFormObject();
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
        $formObject = $this->getMockBuilder(FormObject::class)
            ->setMethods(['calculateHash'])
            ->setConstructorArgs([self::FORM_OBJECT_DEFAULT_CLASS_NAME, self::FORM_OBJECT_DEFAULT_NAME])
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
            ->setConstructorArgs([self::FORM_OBJECT_DEFAULT_CLASS_NAME, self::FORM_OBJECT_DEFAULT_NAME])
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
     * Will inject a basic configuration array, and check that the configuration
     * object created by the form object is valid and without errors.
     *
     * @test
     */
    public function configurationObjectIsCorrectlyBuilt()
    {
        $formObject = $this->getDefaultFormObject();
        $arrayConfiguration = [
            'fields' => [
                'foo' => []
            ]
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
     * Checks that the configuration object is stored in cache, so it is not
     * built every time it is fetched.
     *
     * @test
     */
    public function configurationObjectIsStoredInCache()
    {
        /** @var FormObject|\PHPUnit_Framework_MockObject_MockObject $formObject */
        $formObject = $this->getMockBuilder(FormObject::class)
            ->setMethods(['buildConfigurationObject'])
            ->setConstructorArgs([\stdClass::class, 'foo'])
            ->getMock();

        $formzConfiguration = new Configuration();
        $result = new Result();
        $configurationObjectInstance = new ConfigurationObjectInstance($formzConfiguration, $result);

        $formObject->expects($this->once())
            ->method('buildConfigurationObject')
            ->willReturn($configurationObjectInstance);

        for ($i = 0; $i < 3; $i++) {
            $formObject->getConfigurationObject();
        }

        /** @var FormObject|\PHPUnit_Framework_MockObject_MockObject $formObject2 */
        $formObject2 = $this->getMockBuilder(FormObject::class)
            ->setMethods(['buildConfigurationObject'])
            ->setConstructorArgs([\stdClass::class, 'foo'])
            ->getMock();

        $formObject2->expects($this->never())
            ->method('buildConfigurationObject');

        for ($i = 0; $i < 3; $i++) {
            $formObject2->getConfigurationObject();
        }

        unset($formObject);
        unset($formObject2);
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
        $arrayConfiguration = [
            'fields' => [
                'foo' => []
            ]
        ];

        $formObject->addProperty('foo');
        $formObject->setConfigurationArray($arrayConfiguration);

        $validationResult = $formObject->getConfigurationValidationResult();
        $this->assertInstanceOf(Result::class, $validationResult);
        $this->assertFalse($validationResult->hasErrors());

        unset($formObject);
    }
}
