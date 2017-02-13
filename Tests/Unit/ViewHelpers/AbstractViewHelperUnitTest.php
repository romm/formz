<?php
namespace Romm\Formz\Tests\Unit\ViewHelpers;

use Romm\ConfigurationObject\Tests\Unit\ConfigurationObjectUnitTestUtility;
use Romm\Formz\Tests\Unit\FormzUnitTestUtility;
use TYPO3\CMS\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;

abstract class AbstractViewHelperUnitTest extends ViewHelperBaseTestcase
{
    use ConfigurationObjectUnitTestUtility;
    use FormzUnitTestUtility;

    public function setUp()
    {
        parent::setUp();

        $this->formzSetUp();
    }

    /**
     * After every test, we reset some class which may have change and not be
     * reset correctly.
     */
    public function tearDown()
    {
        $this->formzTearDown();
    }
}
