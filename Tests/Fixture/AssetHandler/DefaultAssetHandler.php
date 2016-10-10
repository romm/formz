<?php
namespace Romm\Formz\Tests\Fixture\AssetHandler;

use Romm\Formz\AssetHandler\AbstractAssetHandler;

class DefaultAssetHandler extends AbstractAssetHandler
{

    /**
     * @param callable $function
     * @return $this
     */
    public function callFunction(callable $function)
    {
        $function();

        return $this;
    }
}
