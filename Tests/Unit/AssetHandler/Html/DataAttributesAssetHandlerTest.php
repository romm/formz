<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\Css;

use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\AssetHandler\Html\DataAttributesAssetHandler;
use Romm\Formz\Core\Core;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Tests\Fixture\Form\ExtendedForm;
use Romm\Formz\Tests\Unit\AssetHandler\AbstractAssetHandlerTestClass;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;

class DataAttributesAssetHandlerTest extends AbstractAssetHandlerTestClass
{

    /**
     * Checks that the field values data attributes are valid.
     *
     * @test
     */
    public function fieldsValuesDataAttributesAreValid()
    {
        $expectedResult = [
            'formz-value-foo' => 'foo',
            'formz-value-bar' => 'john doe'
        ];

        $formObject = Core::get()->getFormObjectFactory()->getInstanceFromClassName(ExtendedForm::class, 'foo');
        $controllerContext = new ControllerContext();
        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);
        $requestResult = new FormResult();
        $form = new ExtendedForm();
        $form->setFoo('foo');
        $form->setBar(['john', 'doe']);

        $dataAttributesValues = DataAttributesAssetHandler::with($assetHandlerFactory)
            ->getFieldsValuesDataAttributes($form, $requestResult);

        $this->assertEquals($expectedResult, $dataAttributesValues);
    }
}
