<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\Css;

use Romm\Formz\AssetHandler\Html\DataAttributesAssetHandler;
use Romm\Formz\Error\Error;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Tests\Fixture\Form\ExtendedForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\AssetHandler\AssetHandlerTestTrait;

class DataAttributesAssetHandlerTest extends AbstractUnitTest
{
    use AssetHandlerTestTrait;

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

        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance(ExtendedForm::class);

        $requestResult = new FormResult();
        $form = new ExtendedForm();
        $form->setFoo('foo');
        $form->setBar(['john', 'doe']);

        /** @var DataAttributesAssetHandler $dataAttributesValuesAssetHandler */
        $dataAttributesValuesAssetHandler = $assetHandlerFactory->getAssetHandler(DataAttributesAssetHandler::class);
        $dataAttributesValues = $dataAttributesValuesAssetHandler->getFieldsValuesDataAttributes($form, $requestResult);

        $this->assertEquals($expectedResult, $dataAttributesValues);

        unset($assetHandlerFactory);
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
        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance(ExtendedForm::class);
        $requestResult = new FormResult();

        foreach ($fieldErrors as $fieldName => $errors) {
            foreach ($errors as $errorData) {
                $error = new Error(
                    $errorData['message'],
                    42,
                    [],
                    '',
                    $errorData['validationName'],
                    $errorData['messageKey']
                );
                $requestResult->forProperty($fieldName)->addError($error);
            }
        }

        /** @var DataAttributesAssetHandler $dataAttributesAssetHandler */
        $dataAttributesAssetHandler = $assetHandlerFactory->getAssetHandler(DataAttributesAssetHandler::class);
        $dataAttributesValues = $dataAttributesAssetHandler->getFieldsErrorsDataAttributes($requestResult);

        $this->assertEquals($expectedResult, $dataAttributesValues);

        unset($assetHandlerFactory);
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
                    DataAttributesAssetHandler::getFieldDataErrorKey('foo')                                 => '1',
                    DataAttributesAssetHandler::getFieldDataValidationErrorKey('foo', 'unknown', 'unknown') => '1'
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
                    DataAttributesAssetHandler::getFieldDataErrorKey('foo')                             => '1',
                    DataAttributesAssetHandler::getFieldDataValidationErrorKey('foo', 'hello', 'world') => '1'
                ],
                [
                    'foo' => [
                        [
                            'message'        => 'foo',
                            'validationName' => 'hello',
                            'messageKey'     => 'world'
                        ]
                    ]
                ]
            ],
            'multipleErrorCheck'       => [
                [
                    DataAttributesAssetHandler::getFieldDataErrorKey('foo')                                 => '1',
                    DataAttributesAssetHandler::getFieldDataValidationErrorKey('foo', 'unknown', 'unknown') => '1',
                    DataAttributesAssetHandler::getFieldDataValidationErrorKey('foo', 'hello', 'world')     => '1'
                ],
                [
                    'foo' => [
                        [
                            'message' => 'foo'
                        ],
                        [
                            'message'        => 'foo',
                            'validationName' => 'hello',
                            'messageKey'     => 'world'
                        ]
                    ]
                ]
            ],
            'multipleFieldsErrorCheck' => [
                [
                    DataAttributesAssetHandler::getFieldDataErrorKey('foo')                                 => '1',
                    DataAttributesAssetHandler::getFieldDataValidationErrorKey('foo', 'unknown', 'unknown') => '1',
                    DataAttributesAssetHandler::getFieldDataErrorKey('bar')                                 => '1',
                    DataAttributesAssetHandler::getFieldDataValidationErrorKey('bar', 'unknown', 'unknown') => '1',
                    DataAttributesAssetHandler::getFieldDataValidationErrorKey('bar', 'hello', 'world')     => '1'
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
                            'message'        => 'bar',
                            'validationName' => 'hello',
                            'messageKey'     => 'world'
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

        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance(ExtendedForm::class);
        $requestResult = new FormResult();

        /** @var DataAttributesAssetHandler $dataAttributesAssetHandler */
        $dataAttributesAssetHandler = $assetHandlerFactory->getAssetHandler(DataAttributesAssetHandler::class);
        $dataAttributesValues = $dataAttributesAssetHandler->getFieldsValidDataAttributes($requestResult);

        $this->assertEquals($expectedResult, $dataAttributesValues);

        unset($assetHandlerFactory);
        unset($requestResult);
    }
}
