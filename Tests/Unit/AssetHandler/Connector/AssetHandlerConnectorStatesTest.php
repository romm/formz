<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\Connector;

use Romm\Formz\AssetHandler\Connector\AssetHandlerConnectorStates;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class AssetHandlerConnectorStatesTest extends AbstractUnitTest
{

    /**
     * Checks that default asset inclusion flag is correctly set and can be
     * retrieved.
     *
     * @test
     */
    public function defaultAssetsInclusionFlagIsSet()
    {
        $assetHandlerConnectorStates = new AssetHandlerConnectorStates;

        $assetHandlerConnectorStates->markDefaultAssetsAsIncluded(true);
        $this->assertTrue($assetHandlerConnectorStates->defaultAssetsWereIncluded());

        $assetHandlerConnectorStates->markDefaultAssetsAsIncluded(false);
        $this->assertFalse($assetHandlerConnectorStates->defaultAssetsWereIncluded());

        unset($assetHandlerConnectorStates);
    }

    /**
     * Checks that adding a validation JavaScript file path will correctly save
     * it so it can be retrieved later.
     *
     * @test
     */
    public function registeringIncludedValidationJavaScriptFilesWorks()
    {
        $assetHandlerConnectorStates = new AssetHandlerConnectorStates;

        $assetHandlerConnectorStates->registerIncludedValidationJavaScriptFiles('foo/bar');
        $this->assertEquals(
            $assetHandlerConnectorStates->getAlreadyIncludedValidationJavaScriptFiles(),
            ['foo/bar']
        );

        unset($assetHandlerConnectorStates);
    }
}
