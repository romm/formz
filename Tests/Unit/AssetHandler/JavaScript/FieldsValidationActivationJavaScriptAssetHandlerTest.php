<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\JavaScript\FieldsValidationActivationJavaScriptAssetHandler;
use Romm\Formz\Condition\Items\FieldIsValidCondition;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\AssetHandler\AssetHandlerTestTrait;

class FieldsValidationActivationJavaScriptAssetHandlerTest extends AbstractUnitTest
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
        $expectedResult = '6df49291aac60427681c1c9db74ef8c9';

        $defaultFormConfiguration = [
            'activationCondition' => [
                'test' => [
                    'type'      => FieldIsValidCondition::CONDITION_NAME,
                    'fieldName' => 'foo'
                ]
            ],
            'fields'              => [
                'foo' => [
                    'validation' => [
                        'required' => [
                            'activation' => [
                                'condition' => 'test'
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $this->setFormConfigurationFromClassName(DefaultForm::class, $defaultFormConfiguration);

        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance(DefaultForm::class);

        /** @var FieldsValidationActivationJavaScriptAssetHandler $fieldsValidationActivationJavaScriptAssetHandler */
        $fieldsValidationActivationJavaScriptAssetHandler = $assetHandlerFactory->getAssetHandler(FieldsValidationActivationJavaScriptAssetHandler::class);
        $javaScriptCode = $fieldsValidationActivationJavaScriptAssetHandler->getFieldsValidationActivationJavaScriptCode();

        $this->assertEquals(
            $expectedResult,
            md5($this->removeMultiLinesComments($this->trimString($javaScriptCode)))
        );

        unset($assetHandlerFactory);
    }
}
