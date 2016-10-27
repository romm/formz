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
        $expectedResult = <<<TXT
(function(){Formz.Form.get('foo',function(form){varfield=null;field=form.getFieldByName('foo');if(null!==field){field.addActivationConditionForValidator('__auto','required',function(field,continueValidation){varflag=false;flag=flag||(Formz.Condition.validateCondition('Romm\\\\Formz\\\\Condition\\\\Items\\\\FieldIsValidCondition',form,{"fieldName":"foo"}));continueValidation(flag);});}form.refreshAllFields();});})();
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
            $this->removeMultiLinesComments($this->trimString($javaScriptCode))
        );

        unset($assetHandlerFactory);
    }
}
