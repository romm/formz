<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\Css;

use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\AssetHandler\Css\FieldsActivationCssAssetHandler;
use Romm\Formz\Condition\Items\FieldIsValidCondition;
use Romm\Formz\Core\Core;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\AssetHandler\AssetHandlerTestTrait;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;

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
        $form = Core::get()->getFormObjectFactory()->getInstanceFromClassName(DefaultForm::class, 'foo');

        $controllerContext = new ControllerContext();
        $assetHandlerFactory = AssetHandlerFactory::get($form, $controllerContext);

        $fieldsActivationCss = FieldsActivationCssAssetHandler::with($assetHandlerFactory)
            ->getFieldsActivationCss();

        $this->assertEquals($this->removeMultiLinesComments($this->trimString($fieldsActivationCss)), $expectedCss);

        unset($form);
        unset($controllerContext);
    }
}
