<?php
namespace Romm\Formz\Tests\Unit;

use Romm\ConfigurationObject\Tests\Unit\ConfigurationObjectUnitTestUtility;
use TYPO3\CMS\Core\Tests\UnitTestCase;

abstract class AbstractUnitTest extends UnitTestCase
{
    use ConfigurationObjectUnitTestUtility;
    use FormzUnitTestUtility;

    protected function setUp()
    {
        $this->initializeConfigurationObjectTestServices();
        $this->setUpFormzCore();
    }
}
