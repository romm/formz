<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\Css;

use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\AssetHandler\Html\DataAttributesAssetHandler;
use Romm\Formz\Core\Core;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Tests\Fixture\Form\ExtendedForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;

class DataAttributesAssetHandlerTest extends AbstractUnitTest
{

    /**
     * Checks that the field values data attributes are valid.
     *
     * @test
     */
    public function fieldsValuesDataAttributesAreValid()
    {
        $expectedResult = [
            DataAttributesAssetHandler::getFieldDataValueKey('foo') => 'foo',
            DataAttributesAssetHandler::getFieldDataValueKey('bar') => 'john doe'
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

        unset($formObject);
        unset($controllerContext);
        unset($requestResult);
    }

    /**
     * Checks that the field error data attributes are valid.
     *
     * @param array $expectedResult
     * @param array $fieldErrors
     *
     * @dataProvider checkFieldsErrorsDataAttributesDataProvider
     * @test
     */
    public function checkFieldsErrorsDataAttributes(array $expectedResult, array $fieldErrors)
    {
        $formObject = Core::get()->getFormObjectFactory()->getInstanceFromClassName(ExtendedForm::class, 'foo');
        $controllerContext = new ControllerContext();
        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);
        $requestResult = new FormResult();

        foreach ($fieldErrors as $fieldName => $errors) {
            foreach ($errors as $errorData) {
                $error = new Error($errorData['message'], 42, [], $errorData['title']);
                $requestResult->forProperty($fieldName)->addError($error);
            }
        }

        $dataAttributesValues = DataAttributesAssetHandler::with($assetHandlerFactory)
            ->getFieldsErrorsDataAttributes($requestResult);

        $this->assertEquals($expectedResult, $dataAttributesValues);

        unset($formObject);
        unset($controllerContext);
        unset($requestResult);
    }

    /**
     * @return array
     */
    public function checkFieldsErrorsDataAttributesDataProvider()
    {
        return [
            'defaultSingleErrorCheck'  => [
                [
                    DataAttributesAssetHandler::getFieldDataErrorKey('foo')                      => '1',
                    DataAttributesAssetHandler::getFieldDataValidationErrorKey('foo', 'default') => '1'
                ],
                [
                    'foo' => [
                        [
                            'message' => 'foo'
                        ]
                    ]
                ]
            ],
            'customSingleErrorCheck'   => [
                [
                    DataAttributesAssetHandler::getFieldDataErrorKey('foo')                          => '1',
                    DataAttributesAssetHandler::getFieldDataValidationErrorKey('foo', 'hello-world') => '1'
                ],
                [
                    'foo' => [
                        [
                            'message' => 'foo',
                            'title'   => 'hello-world'
                        ]
                    ]
                ]
            ],
            'multipleErrorCheck'       => [
                [
                    DataAttributesAssetHandler::getFieldDataErrorKey('foo')                          => '1',
                    DataAttributesAssetHandler::getFieldDataValidationErrorKey('foo', 'default')     => '1',
                    DataAttributesAssetHandler::getFieldDataValidationErrorKey('foo', 'hello-world') => '1'
                ],
                [
                    'foo' => [
                        [
                            'message' => 'foo'
                        ],
                        [
                            'message' => 'foo',
                            'title'   => 'hello-world'
                        ]
                    ]
                ]
            ],
            'multipleFieldsErrorCheck' => [
                [
                    DataAttributesAssetHandler::getFieldDataErrorKey('foo')                          => '1',
                    DataAttributesAssetHandler::getFieldDataValidationErrorKey('foo', 'default')     => '1',
                    DataAttributesAssetHandler::getFieldDataErrorKey('bar')                          => '1',
                    DataAttributesAssetHandler::getFieldDataValidationErrorKey('bar', 'default')     => '1',
                    DataAttributesAssetHandler::getFieldDataValidationErrorKey('bar', 'hello-world') => '1'
                ],
                [
                    'foo' => [
                        [
                            'message' => 'foo'
                        ]
                    ],
                    'bar' => [
                        [
                            'message' => 'bar'
                        ],
                        [
                            'message' => 'bar',
                            'title'   => 'hello-world'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Checks that the field valid data attributes are valid.
     *
     * @test
     */
    public function checkFieldsValidDataAttributes()
    {
        $expectedResult = [
            DataAttributesAssetHandler::getFieldDataValidKey('foo') => '1',
            DataAttributesAssetHandler::getFieldDataValidKey('bar') => '1'
        ];

        $formObject = Core::get()->getFormObjectFactory()->getInstanceFromClassName(ExtendedForm::class, 'foo');
        $controllerContext = new ControllerContext();
        $assetHandlerFactory = AssetHandlerFactory::get($formObject, $controllerContext);
        $requestResult = new FormResult();

        $dataAttributesValues = DataAttributesAssetHandler::with($assetHandlerFactory)
            ->getFieldsValidDataAttributes($requestResult);

        $this->assertEquals($expectedResult, $dataAttributesValues);

        unset($formObject);
        unset($controllerContext);
        unset($requestResult);
    }
}
