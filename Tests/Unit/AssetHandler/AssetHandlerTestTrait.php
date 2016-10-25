<?php
namespace Romm\Formz\Tests\Unit\AssetHandler;

trait AssetHandlerTestTrait
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

    /**
     * Returns the string without CSS comments.
     *
     * @param string $string
     * @return string
     */
    protected function removeCssComments($string)
    {
        return preg_replace('#\/\*((?!\*\/).)*\*\/#', '', $string);
    }
}
