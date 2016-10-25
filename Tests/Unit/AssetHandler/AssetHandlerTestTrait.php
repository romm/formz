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
     * Returns the string without multi-lines comments.
     *
     * @param string $string
     * @return string
     */
    protected function removeMultiLinesComments($string)
    {
        return preg_replace('#\/\*([^*]|[\r\n]|(\*+([^*\/]|[\r\n])))*\*+\/#m', '', $string);
    }
}
