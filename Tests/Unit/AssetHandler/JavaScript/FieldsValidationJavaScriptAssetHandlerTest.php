<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\JavaScript\FieldsValidationJavaScriptAssetHandler;
use Romm\Formz\Condition\Items\FieldIsValidCondition;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\AssetHandler\AssetHandlerTestTrait;
use Romm\Formz\Validation\Validator\RequiredValidator;

class FieldsValidationJavaScriptAssetHandlerTest extends AbstractUnitTest
{

    use AssetHandlerTestTrait;

    /**
     * Checks that the generated JavaScript code is valid, and that the correct
     * JavaScript file paths are returned.
     *
     * @test
     */
    public function checkJavaScriptCode()
    {
        $expectedResult = <<<TXT
(function(){Formz.Form.get('foo',function(form){varfield=null;field=form.getFieldByName('foo');if(null!==field){field.addValidation('required','Romm\\\\Formz\\\\Validation\\\\Validator\\\\RequiredValidator',#CONFIGURATION#);}});})();
TXT;

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
                            'className' => RequiredValidator::class
                        ]
                    ]
                ]
            ]
        ];
        $this->setFormConfigurationFromClassName(DefaultForm::class, $defaultFormConfiguration);

        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance(DefaultForm::class);

        /** @var FieldsValidationJavaScriptAssetHandler|\PHPUnit_Framework_MockObject_MockObject $fieldsValidationJavaScriptAssetHandler */
        $fieldsValidationJavaScriptAssetHandler = $this->getMock(FieldsValidationJavaScriptAssetHandler::class, ['handleValidationConfiguration'], [$assetHandlerFactory]);

        $jsonValidationConfiguration = '';
        $fieldsValidationJavaScriptAssetHandler->method('handleValidationConfiguration')
            ->willReturnCallback(
                function ($validationConfiguration) use (&$jsonValidationConfiguration) {
                    $jsonValidationConfiguration = $validationConfiguration;

                    return $validationConfiguration;
                }
            );

        $fieldsValidationJavaScriptAssetHandler->process();

        $this->assertEquals(RequiredValidator::getJavaScriptValidationFiles(), $fieldsValidationJavaScriptAssetHandler->getJavaScriptValidationFiles());
        $this->assertEquals(
            $this->trimString(str_replace('#CONFIGURATION#', $jsonValidationConfiguration, $expectedResult)),
            $this->removeMultiLinesComments($this->trimString($fieldsValidationJavaScriptAssetHandler->getJavaScriptCode()))
        );

        unset($assetHandlerFactory);
    }
}
