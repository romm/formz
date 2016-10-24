<?php
namespace Romm\Formz\Tests\Unit\AssetHandler;

use Romm\Formz\Tests\Unit\AbstractUnitTest;

class AbstractAssetHandlerTestClass extends AbstractUnitTest
{

    /**
     * Returns the same string, but without any space/tab/new line.
     *
     * @param string $string
     * @return string
     */
    protected function trimString($string)
    {
        return preg_replace('/\s+/', '', $string);
    }

    protected function removeCssComments($string)
    {
        return preg_replace('#\/\*((?!\*\/).)*\*\/#', '', $string);
    }
}
