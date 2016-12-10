<?php
namespace Romm\Formz\Tests\Unit;

use Romm\ConfigurationObject\Tests\Unit\ConfigurationObjectUnitTestUtility;
use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\Condition\ConditionFactory;
use Romm\Formz\Form\FormObject;
use TYPO3\CMS\Core\Tests\UnitTestCase;

abstract class AbstractUnitTest extends UnitTestCase
{

    use ConfigurationObjectUnitTestUtility;
    use FormzUnitTestUtility;

    const FORM_OBJECT_DEFAULT_CLASS_NAME = \stdClass::class;
    const FORM_OBJECT_DEFAULT_NAME = 'foo';

    protected function setUp()
    {
        $this->initializeConfigurationObjectTestServices();
        $this->setUpFormzCore();

        ConditionFactory::get()->registerDefaultConditions();
    }

    /**
     * @return FormObject
     */
    protected function getFormObject()
    {
        return new FormObject(self::FORM_OBJECT_DEFAULT_CLASS_NAME, self::FORM_OBJECT_DEFAULT_NAME);
    }

    /**
     * After every test, we reset some class which may have change and not be
     * reset correctly.
     */
    protected function tearDown()
    {
        // Reset asset handler factory instances.
        $reflectedCore = new \ReflectionClass(AssetHandlerFactory::class);
        $objectManagerProperty = $reflectedCore->getProperty('factoryInstances');
        $objectManagerProperty->setAccessible(true);
        $objectManagerProperty->setValue([]);
        $objectManagerProperty->setAccessible(false);
    }
}
