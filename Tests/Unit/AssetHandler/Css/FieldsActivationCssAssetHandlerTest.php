<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\Css;

use Romm\Formz\AssetHandler\Css\FieldsActivationCssAssetHandler;
use Romm\Formz\Condition\Items\FieldIsValidCondition;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\AssetHandler\AssetHandlerTestTrait;

class FieldsActivationCssAssetHandlerTest extends AbstractUnitTest
{
    use AssetHandlerTestTrait;

    /**
     * Checks that the CSS code returned by the asset handler is valid.
     *
     * @test
     */
    public function fieldsActivationCssIsValid()
    {
        $expectedCss = 'form[name="foo"][formz-field-container="foo"]{display:none;}form[name="foo"][formz-valid-foo="1"][formz-field-container="foo"]{display:block;}';

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

        /** @var FieldsActivationCssAssetHandler $fieldsActivationCssAssetHandler */
        $fieldsActivationCssAssetHandler = $assetHandlerFactory->getAssetHandler(FieldsActivationCssAssetHandler::class);
        $fieldsActivationCss = $fieldsActivationCssAssetHandler->getFieldsActivationCss();

        $this->assertEquals($this->removeMultiLinesComments($this->trimString($fieldsActivationCss)), $expectedCss);

        unset($assetHandlerFactory);
    }
}
