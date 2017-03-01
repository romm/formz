<?php
namespace Romm\Formz\Tests\Unit\AssetHandler\Css;

use Romm\Formz\AssetHandler\Html\DataAttributesAssetHandler;
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
     * @param array $fieldMessages
     *
     * @dataProvider checkFieldsErrorsDataAttributesDataProvider
     * @test
     */
    public function checkFieldsErrorsDataAttributes(array $expectedResult, array $fieldMessages)
    {
        $assetHandlerFactory = $this->getAssetHandlerFactoryInstance(ExtendedForm::class);
        $requestResult = new FormResult();

        foreach ($fieldMessages as $fieldName => $messages) {
            foreach ($messages as $data) {
                $type = 'Romm\\Formz\\Error\\' . ucfirst($data['type']);
                $addMethod = 'add' . ucfirst($data['type']);

                $message = new $type(
                    $data['message'],
                    42,
                    $data['validationName'],
                    $data['messageKey'],
                    [],
                    ''
                );
                $requestResult->forProperty($fieldName)->$addMethod($message);
            }
        }

        /** @var DataAttributesAssetHandler $dataAttributesAssetHandler */
        $dataAttributesAssetHandler = $assetHandlerFactory->getAssetHandler(DataAttributesAssetHandler::class);
        $dataAttributesValues = $dataAttributesAssetHandler->getFieldsMessagesDataAttributes($requestResult);

        $this->assertEquals($expectedResult, $dataAttributesValues);

        unset($assetHandlerFactory);
        unset($requestResult);
    }

    /**
     * @return array
     */
    public function checkFieldsErrorsDataAttributesDataProvider()
    {
        $this->injectAllDependencies();

        return [
            'defaultSingleErrorCheck'  => [
                [
                    DataAttributesAssetHandler::getFieldDataMessageKey('foo')                                          => '1',
                    DataAttributesAssetHandler::getFieldDataValidationMessageKey('foo', 'error', 'unknown', 'unknown') => '1'
                ],
                [
                    'foo' => [
                        [
                            'type'    => 'error',
                            'message' => 'foo'
                        ]
                    ]
                ]
            ],
            'customSingleErrorCheck'   => [
                [
                    DataAttributesAssetHandler::getFieldDataMessageKey('foo')                                      => '1',
                    DataAttributesAssetHandler::getFieldDataValidationMessageKey('foo', 'error', 'hello', 'world') => '1'
                ],
                [
                    'foo' => [
                        [
                            'type'           => 'error',
                            'message'        => 'foo',
                            'validationName' => 'hello',
                            'messageKey'     => 'world'
                        ]
                    ]
                ]
            ],
            'multipleErrorCheck'       => [
                [
                    DataAttributesAssetHandler::getFieldDataMessageKey('foo')                                          => '1',
                    DataAttributesAssetHandler::getFieldDataValidationMessageKey('foo', 'error', 'unknown', 'unknown') => '1',
                    DataAttributesAssetHandler::getFieldDataValidationMessageKey('foo', 'error', 'hello', 'world')     => '1'
                ],
                [
                    'foo' => [
                        [
                            'type'    => 'error',
                            'message' => 'foo'
                        ],
                        [
                            'type'           => 'error',
                            'message'        => 'foo',
                            'validationName' => 'hello',
                            'messageKey'     => 'world'
                        ]
                    ]
                ]
            ],
            'multipleFieldsErrorCheck' => [
                [
                    DataAttributesAssetHandler::getFieldDataMessageKey('foo')                                            => '1',
                    DataAttributesAssetHandler::getFieldDataValidationMessageKey('foo', 'error', 'unknown', 'unknown')   => '1',
                    DataAttributesAssetHandler::getFieldDataMessageKey('bar')                                            => '1',
                    DataAttributesAssetHandler::getFieldDataValidationMessageKey('bar', 'error', 'unknown', 'unknown')   => '1',
                    DataAttributesAssetHandler::getFieldDataValidationMessageKey('bar', 'error', 'hello', 'world')       => '1',
                    DataAttributesAssetHandler::getFieldDataMessageKey('bar', 'warning')                                 => '1',
                    DataAttributesAssetHandler::getFieldDataValidationMessageKey('bar', 'warning', 'unknown', 'unknown') => '1',
                    DataAttributesAssetHandler::getFieldDataMessageKey('bar', 'notice')                                  => '1',
                    DataAttributesAssetHandler::getFieldDataValidationMessageKey('bar', 'notice', 'unknown', 'unknown')  => '1'
                ],
                [
                    'foo' => [
                        [
                            'type'    => 'error',
                            'message' => 'foo'
                        ]
                    ],
                    'bar' => [
                        [
                            'type'    => 'error',
                            'message' => 'bar'
                        ],
                        [
                            'type'           => 'error',
                            'message'        => 'bar',
                            'validationName' => 'hello',
                            'messageKey'     => 'world'
                        ],
                        [
                            'type'    => 'warning',
                            'message' => 'bar'
                        ],
                        [
                            'type'    => 'notice',
                            'message' => 'bar'
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
