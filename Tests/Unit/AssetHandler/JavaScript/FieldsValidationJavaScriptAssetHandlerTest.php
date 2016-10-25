<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\AssetHandler\JavaScript\FieldsValidationJavaScriptAssetHandler;
use Romm\Formz\Condition\Items\FieldIsValidCondition;
use Romm\Formz\Core\Core;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\AssetHandler\AssetHandlerTestTrait;
use Romm\Formz\Validation\Validator\RequiredValidator;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;

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
        // MD5 of the JavaScript code result.
        $expectedResult = '4ce1221868d92d2a9ee626e01de8c5ee';

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

        $formObject = Core::get()->getFormObjectFactory()->getInstanceFromClassName(DefaultForm::class, 'foo');
        $controllerContext = new ControllerContext();
        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);

        $assetHandler = FieldsValidationJavaScriptAssetHandler::with($assetHandlerFactory)
            ->process();

        $this->assertEquals(RequiredValidator::getJavaScriptValidationFiles(), $assetHandler->getJavaScriptValidationFiles());
        $this->assertEquals(
            $expectedResult,
            md5($this->removeMultiLinesComments($this->trimString($assetHandler->getJavaScriptCode())))
        );
    }
}
