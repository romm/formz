<?php
namespace Romm\Formz\Tests\Unit;

use Romm\ConfigurationObject\Tests\Unit\ConfigurationObjectUnitTestUtility;
use TYPO3\CMS\Core\Tests\UnitTestCase;

abstract class AbstractUnitTest extends UnitTestCase
{
    use ConfigurationObjectUnitTestUtility;
    use FormzUnitTestUtility;

    const FORM_OBJECT_DEFAULT_CLASS_NAME = \stdClass::class;
    const FORM_OBJECT_DEFAULT_NAME = 'foo';

    protected function setUp()
    {
        $this->formzSetUp();
    }

    /**
     * After every test, we reset some class which may have change and not be
     * reset correctly.
     */
    protected function tearDown()
    {
        $this->formzTearDown();
    }
}
