<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\AssetHandler\JavaScript\FieldsActivationJavaScriptAssetHandler;
use Romm\Formz\Condition\Items\FieldIsValidCondition;
use Romm\Formz\Core\Core;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;

class FieldsActivationJavaScriptAssetHandlerTest extends AbstractUnitTest
{

    /**
     * Checks that the generated JavaScript code is valid.
     *
     * @test
     */
    public function checkJavaScriptCode()
    {
        // MD5 of the JavaScript code result.
        $expectedResult = 'e1f38c2417b4f693242e63a01cc297ae';

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

        $formObject = Core::get()->getFormObjectFactory()->getInstanceFromClassName(DefaultForm::class, 'foo');
        $controllerContext = new ControllerContext();
        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);

        $javaScriptCode = FieldsActivationJavaScriptAssetHandler::with($assetHandlerFactory)
            ->getFieldsActivationJavaScriptCode();

        $this->assertEquals($expectedResult, md5($javaScriptCode));
    }
}
