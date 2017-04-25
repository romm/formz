<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\JavaScript\FieldsValidationJavaScriptAssetHandler;
use Romm\Formz\Form\Definition\Field\Validation\Validation;
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
(function(){Fz.Form.get('foo',function(form){varfield=null;field=form.getFieldByName('foo');if(null!==field){field.addValidation('validation-name','Romm\\\\Formz\\\\Validation\\\\Validator\\\\RequiredValidator',{"options":[],"messages":{"default":"RommFormzTestsFixtureFormDefaultForm-foo-validation-name-default"},"settings":{"className":"Romm\\\\Formz\\\\Validation\\\\Validator\\\\RequiredValidator","priority":null,"options":[],"messages":[],"activation":{"expression":null,"conditions":[]},"useAjax":false,"name":"validation-name"},"acceptsEmptyValues":false});}});})();
TXT;

        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance();

        /** @var FieldsValidationJavaScriptAssetHandler|\PHPUnit_Framework_MockObject_MockObject $assetHandler */
        $assetHandler = $this->getMockBuilder(FieldsValidationJavaScriptAssetHandler::class)
            ->setMethods(['handleValidationConfiguration'])
            ->setConstructorArgs([$assetHandlerFactory])
            ->getMock();

        $jsonValidationConfiguration = '';
        $assetHandler->expects($this->once())
            ->method('handleValidationConfiguration')
            ->willReturnCallback(
                function ($validationConfiguration) use (&$jsonValidationConfiguration) {
                    $jsonValidationConfiguration = $validationConfiguration;

                    return $validationConfiguration;
                }
            );

        $field = $assetHandlerFactory->getFormObject()->getDefinition()->getField('foo');
        $validation = new Validation;
        $validation->setClassName(RequiredValidator::class);
        $validation->setName('validation-name');
        $field->addValidation($validation);

        $this->assertEquals(RequiredValidator::getJavaScriptValidationFiles(), $assetHandler->getJavaScriptValidationFiles());

        $javaScriptCode = $assetHandler->getJavaScriptCode();
        $this->assertNotNull($jsonValidationConfiguration);

        $this->assertEquals(
            $this->trimString(str_replace('#CONFIGURATION#', $jsonValidationConfiguration, $expectedResult)),
            $this->removeMultiLinesComments($this->trimString($javaScriptCode))
        );

        unset($assetHandlerFactory);
    }
}
