<?php
namespace Romm\Formz\Tests\Unit\Configuration;

use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Form\FormObject;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class ConfigurationTest extends AbstractUnitTest
{

    /**
     * Checks that the static function `getConfigurationObjectServices` needed
     * by the `configuration_object` API returns a valid class.
     *
     * @test
     */
    public function configurationObjectServicesAreValid()
    {
        $serviceFactory = Configuration::getConfigurationObjectServices();

        $this->assertInstanceOf(ServiceFactory::class, $serviceFactory);

        unset($serviceFactory);
    }

    /**
     * Checks that a form can be added properly to the list.
     *
     * @test
     */
    public function formCanBeAdded()
    {
        $className = \stdClass::class;
        $name = 'foo';

        $configuration = new Configuration();
        $formObject = new FormObject($className, $name);

        $this->assertFalse($configuration->hasForm($className, $name));

        $configuration->addForm($formObject);

        $this->assertTrue($configuration->hasForm($className, $name));
        $this->assertSame($formObject, $configuration->getForm($className, $name));

        unset($configuration);
        unset($formObject);
    }

    /**
     * Adding two forms with the same name and class name must throw an
     * exception.
     *
     * @test
     */
    public function addingSameFormTwoTimesThrowsException()
    {
        $className = \stdClass::class;
        $name = 'foo';

        $configuration = new Configuration();
        $formObject = new FormObject($className, $name);
        $formObject2 = new FormObject($className, $name);

        $this->setExpectedException(DuplicateEntryException::class);

        $configuration->addForm($formObject);
        $configuration->addForm($formObject2);

        unset($configuration);
        unset($formObject);
        unset($formObject2);
    }
}
