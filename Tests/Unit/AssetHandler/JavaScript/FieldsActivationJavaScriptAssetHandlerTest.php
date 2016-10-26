<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\JavaScript\FieldsActivationJavaScriptAssetHandler;
use Romm\Formz\Condition\Items\FieldIsValidCondition;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\AssetHandler\AssetHandlerTestTrait;

class FieldsActivationJavaScriptAssetHandlerTest extends AbstractUnitTest
{

    use AssetHandlerTestTrait;

    /**
     * Checks that the generated JavaScript code is valid.
     *
     * @test
     */
    public function checkJavaScriptCode()
    {
        // MD5 of the JavaScript code result.
        $expectedResult = '2356be2c08235e95dccbcf5532c87184';

        $defaultFormConfiguration = [
            'activationCondition' => [
                'test' => [
                    'type'      => FieldIsValidCondition::CONDITION_NAME,
                    'fieldName' => 'foo'
                ]
            ],
            'fields'              => [
                'foo' => [
                    'activation' => [
                        'condition' => 'test'
                    ]
                ]
            ]
        ];
        $this->setFormConfigurationFromClassName(DefaultForm::class, $defaultFormConfiguration);

        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance(DefaultForm::class);

        /** @var FieldsActivationJavaScriptAssetHandler $fieldsActivationJavaScriptAssetHandler */
        $fieldsActivationJavaScriptAssetHandler = $assetHandlerFactory->getAssetHandler(FieldsActivationJavaScriptAssetHandler::class);
        $javaScriptCode = $fieldsActivationJavaScriptAssetHandler->getFieldsActivationJavaScriptCode();

        $this->assertEquals(
            $expectedResult,
            md5($this->removeMultiLinesComments($this->trimString($javaScriptCode)))
        );

        unset($assetHandlerFactory);
    }
}
