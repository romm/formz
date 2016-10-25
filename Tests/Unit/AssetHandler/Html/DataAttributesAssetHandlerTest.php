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

        unset($formObject);
        unset($controllerContext);
        unset($requestResult);
    }

    /**
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
    }

    /**
     * @return array
     */
    public function checkFieldsErrorsDataAttributesDataProvider()
    {
        return [
            'defaultSingleErrorCheck' => [
                [
                    'formz-error-foo'         => '1',
                    'formz-error-foo-default' => '1'
                ],
                [
                    'foo' => [
                        [
                            'message' => 'foo'
                        ]
                    ]
                ]
            ],
            'customSingleErrorCheck'  => [
                [
                    'formz-error-foo'             => '1',
                    'formz-error-foo-hello-world' => '1'
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
            'multipleErrorCheck'  => [
                [
                    'formz-error-foo'             => '1',
                    'formz-error-foo-default'     => '1',
                    'formz-error-foo-hello-world' => '1'
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
            'multipleFieldsErrorCheck'  => [
                [
                    'formz-error-foo'             => '1',
                    'formz-error-foo-default'     => '1',
                    'formz-error-bar'             => '1',
                    'formz-error-bar-default'     => '1',
                    'formz-error-bar-hello-world' => '1'
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
}
