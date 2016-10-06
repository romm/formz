<?php
namespace Romm\Formz\Tests\Unit;

use TYPO3\CMS\Core\Tests\UnitTestCase;

abstract class AbstractUnitTest extends UnitTestCase
{
    use FormzUnitTestUtility;

    protected function setUp()
    {
        $this->setUpFormzCore();
    }
}
