<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\JavaScript\FormzLocalizationJavaScriptAssetHandler;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\AssetHandler\AssetHandlerTestTrait;
use Romm\Formz\Validation\Validator\RequiredValidator;

class FormzLocalizationJavaScriptAssetHandlerTest extends AbstractUnitTest
{

    use AssetHandlerTestTrait;

    /**
     * Checks that all processes from this asset handler are correctly run.
     *
     * @test
     */
    public function checkLocalizationIsInitializedCorrectly()
    {
        $expectedJavaScriptCode = <<<TXT
Formz.Localization.addLocalization(#REAL_TRANSLATIONS#, #TRANSLATIONS_BINDING#);
TXT;

        $defaultFormConfiguration = [
            'fields'              => [
                'foo' => [
                    'validation' => [
                        'required' => [
                            'className' => RequiredValidator::class
                        ]
                    ]
                ]
            ]
        ];
        $this->setFormConfigurationFromClassName(DefaultForm::class, $defaultFormConfiguration);

        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance(DefaultForm::class);

        /** @var FormzLocalizationJavaScriptAssetHandler|\PHPUnit_Framework_MockObject_MockObject $formzLocalizationJavaScriptAssetHandler */
        $formzLocalizationJavaScriptAssetHandler = $this->getMock(
            FormzLocalizationJavaScriptAssetHandler::class,
            ['handleRealTranslations', 'handleTranslationsBinding'],
            [$assetHandlerFactory]
        );

        $jsonRealTranslations = '';
        $jsonTranslationsBinding = '';

        $formzLocalizationJavaScriptAssetHandler->method('handleRealTranslations')
            ->willReturnCallback(function($realTranslations) use (&$jsonRealTranslations) {
                $jsonRealTranslations = $realTranslations;

                return $realTranslations;
            });

        $formzLocalizationJavaScriptAssetHandler->method('handleTranslationsBinding')
            ->willReturnCallback(function($translationsBinding) use (&$jsonTranslationsBinding) {
                $jsonTranslationsBinding = $translationsBinding;

                return $translationsBinding;
            });

        $formzLocalizationJavaScriptAssetHandler->injectTranslationsForFormFieldsValidation();

        $translationKeys = $formzLocalizationJavaScriptAssetHandler->getTranslationKeysForFieldValidation($assetHandlerFactory->getFormObject()->getConfiguration()->getField('foo'), 'required');

        $javaScriptCode = $formzLocalizationJavaScriptAssetHandler->getJavaScriptCode();

        $expectedJavaScriptCode = str_replace('#REAL_TRANSLATIONS#', $jsonRealTranslations, $expectedJavaScriptCode);
        $expectedJavaScriptCode = str_replace('#TRANSLATIONS_BINDING#', $jsonTranslationsBinding, $expectedJavaScriptCode);

        $this->assertEquals(
            $this->trimString($expectedJavaScriptCode),
            $this->removeMultiLinesComments($this->trimString($javaScriptCode))
        );

        $realTranslations = json_decode($jsonRealTranslations, true);
        $translationsBinding = json_decode($jsonTranslationsBinding, true);

        $this->assertNotNull($realTranslations);
        $this->assertNotNull($translationsBinding);

        $requiredDefaultKey = $translationKeys['default'];

        $this->assertTrue(array_key_exists($requiredDefaultKey, $translationsBinding));

        $requiredDefaultHash = $translationsBinding[$requiredDefaultKey];

        $this->assertTrue(array_key_exists($requiredDefaultHash, $realTranslations));
    }
}
