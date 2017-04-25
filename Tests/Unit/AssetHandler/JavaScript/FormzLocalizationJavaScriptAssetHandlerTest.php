<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\JavaScript\FormzLocalizationJavaScriptAssetHandler;
use Romm\Formz\Form\Definition\Field\Validation\Validation;
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
Fz.Localization.addLocalization(#REAL_TRANSLATIONS#, #TRANSLATIONS_BINDING#);
TXT;

        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance();

        $field = $assetHandlerFactory->getFormObject()->getDefinition()->getField('foo');
        $validation = new Validation;
        $validation->setName('validation-name');
        $validation->setClassName(RequiredValidator::class);
        $field->addValidation($validation);

            /** @var FormzLocalizationJavaScriptAssetHandler|\PHPUnit_Framework_MockObject_MockObject $assetHandler */
        $assetHandler = $this->getMockBuilder(FormzLocalizationJavaScriptAssetHandler::class)
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

        $assetHandler->injectTranslationsForFormFieldsValidation();

        $translationKeys = $assetHandler->getTranslationKeysForFieldValidation($field, 'validation-name');

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
