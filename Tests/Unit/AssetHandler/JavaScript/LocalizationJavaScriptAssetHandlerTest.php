<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\JavaScript\LocalizationJavaScriptAssetHandler;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\AssetHandler\AssetHandlerTestTrait;
use Romm\Formz\Validation\Validator\RequiredValidator;

class LocalizationJavaScriptAssetHandlerTest extends AbstractUnitTest
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
Fz.Localization.addLocalization(#REAL_TRANSLATIONS#, #TRANSLATIONS_BINDING#);
TXT;

        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance();

        $field = $assetHandlerFactory->getFormObject()->getDefinition()->getField('foo');
        $validator = $field->addValidator('validation-name', RequiredValidator::class);

            /** @var LocalizationJavaScriptAssetHandler|\PHPUnit_Framework_MockObject_MockObject $assetHandler */
        $assetHandler = $this->getMockBuilder(LocalizationJavaScriptAssetHandler::class)
            ->setMethods(['handleRealTranslations', 'handleTranslationsBinding'])
            ->setConstructorArgs([$assetHandlerFactory])
            ->getMock();

        $jsonRealTranslations = '';
        $jsonTranslationsBinding = '';

        $assetHandler->method('handleRealTranslations')
            ->willReturnCallback(function ($realTranslations) use (&$jsonRealTranslations) {
                $jsonRealTranslations = $realTranslations;

                return $realTranslations;
            });

        $assetHandler->method('handleTranslationsBinding')
            ->willReturnCallback(function ($translationsBinding) use (&$jsonTranslationsBinding) {
                $jsonTranslationsBinding = $translationsBinding;

                return $translationsBinding;
            });

        $assetHandler->injectTranslationsForFormFieldsValidator();

        $translationKeys = $assetHandler->getTranslationKeysForFieldValidator($field, $validator);

        $javaScriptCode = $assetHandler->getJavaScriptCode();

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
