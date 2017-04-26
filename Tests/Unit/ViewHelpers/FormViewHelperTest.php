<?php
namespace Romm\Formz\Tests\Unit\ViewHelpers;

use Romm\Formz\AssetHandler\Connector\AssetHandlerConnectorManager;
use Romm\Formz\AssetHandler\Connector\CssAssetHandlerConnector;
use Romm\Formz\AssetHandler\Connector\JavaScriptAssetHandlerConnector;
use Romm\Formz\AssetHandler\Html\DataAttributesAssetHandler;
use Romm\Formz\Core\Core;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Exceptions\ClassNotFoundException;
use Romm\Formz\Exceptions\InvalidOptionValueException;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Form\FormObject\FormObjectProxy;
use Romm\Formz\Service\ControllerService;
use Romm\Formz\Service\ViewHelper\FormViewHelperService;
use Romm\Formz\Service\ViewHelper\Legacy\FormViewHelper;
use Romm\Formz\Service\ViewHelper\Legacy\OldFormViewHelper;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Fixture\Form\ExtendedForm;
use Romm\Formz\Validation\Validator\Form\DefaultFormValidator;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder;

class FormViewHelperTest extends AbstractViewHelperUnitTest
{
    /**
     * If the TypoScript configuration is not included, the result must be an
     * empty string.
     *
     * @test
     */
    public function typoScriptNotIncludedWillReturnEmptyString()
    {
        $this->addFormzConfiguration([
            'settings' => ['typoScriptIncluded' => null]
        ]);

        $viewHelper = $this->getFormViewHelperMock(['getFormClassName']);
        $viewHelper->initializeArguments();

        $result = $viewHelper->render();

        $this->assertEquals('', $result);
    }

    /**
     * If the TypoScript configuration is not included, and the debug mode is
     * enabled, the result must be a message.
     *
     * @test
     */
    public function typoScriptNotIncludedInDebugModeWillReturnMessage()
    {
        $this->setExtensionConfigurationValue('debugMode', true);
        $this->addFormzConfiguration([
            'settings' => ['typoScriptIncluded' => null]
        ]);

        $viewHelper = $this->getFormViewHelperMock(['getFormClassName']);
        $viewHelper->initializeArguments();

        $result = $viewHelper->render();

        $this->assertEquals('LLL::form.typoscript_not_included.error_message', $result);
    }

    /**
     * If the value sent in the argument `object` is not an object, an exception
     * must be thrown.
     *
     * @test
     */
    public function formArgumentIsNotAnObjectThrowsException()
    {
        $this->setExpectedException(InvalidOptionValueException::class, '', 1490713939);

        $viewHelper = $this->getFormViewHelperMock(['getFormClassName']);

        $viewHelper->setArguments(['object' => true]);
        $viewHelper->initializeArguments();
        $viewHelper->initialize();
    }

    /**
     * If the value sent in the argument `object` is not an instance of
     * `FormInterface`, an exception must be thrown.
     *
     * @test
     */
    public function formArgumentIsNotFormThrowsException()
    {
        $this->setExpectedException(InvalidOptionValueException::class, '', 1490714346);

        $viewHelper = $this->getFormViewHelperMock(['getFormClassName']);

        $viewHelper->setArguments(['object' => new \stdClass]);
        $viewHelper->initializeArguments();
        $viewHelper->initialize();
    }

    /**
     * If the value sent in the argument `object` is not an instance of the
     * expected form class (fetched from the controller action argument), an
     * exception must be thrown.
     *
     * @test
     */
    public function formArgumentIsNotExpectedClassThrowsException()
    {
        $this->setExpectedException(InvalidOptionValueException::class, '', 1490714534);

        $viewHelper = $this->getFormViewHelperMock(['getFormClassName']);

        $viewHelper->method('getFormClassName')
            ->willReturn(ExtendedForm::class);

        $viewHelper->setArguments(['object' => new DefaultForm]);
        $viewHelper->initializeArguments();
        $viewHelper->initialize();
    }

    /**
     * The argument `form` given to the view helper should be attached to the
     * `FormObject` if it is a valid form instance, and the form was not
     * submitted.
     *
     * @test
     */
    public function formArgumentIsGivenToFormObject()
    {
        $form = new DefaultForm;

        $viewHelper = $this->getFormViewHelperMock(['getFormClassName']);

        $viewHelper->method('getFormClassName')
            ->willReturn(DefaultForm::class);

        $viewHelper->expects($this->once())
            ->method('getFormObject')
            ->with($form);

        $viewHelper->setArguments(['object' => $form]);
        $viewHelper->initializeArguments();
        $viewHelper->initialize();
    }

    /**
     * Checks that every single asset handler method is called during the form
     * rendering.
     *
     * @test
     */
    public function assetHandlerMethodsAreCalledCorrectly()
    {
        $viewHelper = $this->getFormViewHelperMock(['getAssetHandlerConnectorManager', 'getFormClassName', 'getFormInstance']);

        $viewHelper->method('getFormInstance')
            ->willReturn(new DefaultForm);

        $viewHelper->initializeArguments();
        $viewHelper->initialize();

        $assetHandlerConnectorManagerMock = $this->getMockBuilder(AssetHandlerConnectorManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['includeDefaultAssets', 'getJavaScriptAssetHandlerConnector', 'getCssAssetHandlerConnector'])
            ->getMock();

        $assetHandlerConnectorManagerMock->expects($this->once())
            ->method('includeDefaultAssets');

        $javaScriptAssetHandlerMethods = [
            'generateAndIncludeFormzConfigurationJavaScript',
            'generateAndIncludeJavaScript',
            'generateAndIncludeInlineJavaScript',
            'includeJavaScriptValidationAndConditionFiles',
            'includeLanguageJavaScriptFiles'
        ];

        $javaScriptAssetHandlerConnectorMock = $this->getMockBuilder(JavaScriptAssetHandlerConnector::class)
            ->disableOriginalConstructor()
            ->setMethods($javaScriptAssetHandlerMethods)
            ->getMock();

        foreach ($javaScriptAssetHandlerMethods as $method) {
            $javaScriptAssetHandlerConnectorMock->expects($this->once())
                ->method($method)
                ->willReturnSelf();
        }

        $assetHandlerConnectorManagerMock->method('getJavaScriptAssetHandlerConnector')
            ->willReturn($javaScriptAssetHandlerConnectorMock);

        $cssAssetHandlerConnectorMock = $this->getMockBuilder(CssAssetHandlerConnector::class)
            ->disableOriginalConstructor()
            ->setMethods(['includeGeneratedCss'])
            ->getMock();

        $cssAssetHandlerConnectorMock->expects($this->once())
            ->method('includeGeneratedCss')
            ->willReturnSelf();

        $assetHandlerConnectorManagerMock->method('getCssAssetHandlerConnector')
            ->willReturn($cssAssetHandlerConnectorMock);

        $viewHelper->method('getAssetHandlerConnectorManager')
            ->willReturn($assetHandlerConnectorManagerMock);

        $viewHelper->render();
    }

    /**
     * Checks that the default class value configured in the TypoScript
     * configuration is added to the form tag.
     *
     * @test
     */
    public function defaultClassIsAddedToForm()
    {
        $defaultClass = 'default-class';
        $formObject = $this->getDefaultFormObject();

        $viewHelper = $this->getFormViewHelperMock(
            [
                'handleDataAttributes',
                'handleAssets',
                'getFormClassName',
                'getFormInstance'
            ],
            $formObject
        );

        $viewHelper->method('getFormInstance')
            ->willReturn(new DefaultForm);

        $viewHelper->initializeArguments();
        $viewHelper->initialize();

        $formObject->getDefinition()
            ->getSettings()
            ->setDefaultClass($defaultClass);

        $tagBuilderMock = $this->getMockBuilder(TagBuilder::class)
            ->setMethods(['addAttribute'])
            ->getMock();

        $tagBuilderMock->expects($this->once())
            ->method('addAttribute')
            ->with('class', $defaultClass);

        $this->inject($viewHelper, 'tag', $tagBuilderMock);

        $viewHelper->render();
    }

    /**
     * Checks that the fields values are added in the data attributes of the
     * form HTML tag.
     *
     * @test
     */
    public function fieldsValuesDataAttributesAreAdded()
    {
        $fieldsValuesDataAttributes = ['data-attributes-values' => 'foo'];
        $mergedDataAttributes = array_merge($fieldsValuesDataAttributes);

        $formObject = $this->getDefaultFormObject();
        $formObject->setForm(new DefaultForm);

        $formService = new FormViewHelperService;
        $formService->setFormObject($formObject);

        $viewHelper = $this->getFormViewHelperMock(
            [
                'addDefaultClass',
                'handleAssets',
                'getDataAttributesAssetHandler',
                'getFormClassName',
                'getFormInstance'
            ],
            $formObject
        );

        $viewHelper->method('getFormInstance')
            ->willReturn(new DefaultForm);

        $viewHelper->initializeArguments();
        $viewHelper->initialize();
        $viewHelper->injectFormService($formService);

        $tagBuilder = $this->getMockBuilder(TagBuilder::class)
            ->setMethods(['addAttributes'])
            ->getMock();
        $this->inject($viewHelper, 'tag', $tagBuilder);

        $tagBuilder->expects($this->exactly(1))
            ->method('addAttributes')
            ->with($mergedDataAttributes);

        $viewHelper->expects($this->once())
            ->method('getDataAttributesAssetHandler')
            ->willReturnCallback(function () use ($fieldsValuesDataAttributes) {
                $assetHandlerMock = $this->getMockBuilder(DataAttributesAssetHandler::class)
                    ->disableOriginalConstructor()
                    ->setMethods(['getFieldsValuesDataAttributes', 'getFieldsValidDataAttributes', 'getFieldsMessagesDataAttributes'])
                    ->getMock();

                $assetHandlerMock->expects($this->once())
                    ->method('getFieldsValuesDataAttributes')
                    ->willReturn($fieldsValuesDataAttributes);

                $assetHandlerMock->expects($this->never())
                    ->method('getFieldsValidDataAttributes');

                $assetHandlerMock->expects($this->never())
                    ->method('getFieldsMessagesDataAttributes');

                return $assetHandlerMock;
            });

        $viewHelper->render();
    }

    /**
     * Checks that all data attributes that can be added for a field are added
     * to the form HTML tag.
     *
     * @test
     */
    public function fieldsAllDataAttributesAreAdded()
    {
        $fieldsValuesDataAttributes = ['data-attributes-values' => 'foo'];
        $fieldsValidDataAttributes = ['data-attributes-valid' => 'foo'];
        $fieldsMessagesDataAttributes = ['data-attributes-messages' => 'foo'];
        $mergedDataAttributes = array_merge(
            $fieldsValuesDataAttributes,
            [DataAttributesAssetHandler::getFieldSubmissionDone() => '1'],
            $fieldsValidDataAttributes,
            $fieldsMessagesDataAttributes
        );

        $formObject = $this->getDefaultFormObject(function (FormObjectProxy $proxy) {
            $proxy->markFormAsSubmitted();
            $proxy->markFormAsValidated();
        });
        $formObject->setForm(new DefaultForm);

        $formService = new FormViewHelperService;
        $formService->setFormObject($formObject);

        $viewHelper = $this->getFormViewHelperMock(
            [
                'addDefaultClass',
                'handleAssets',
                'getDataAttributesAssetHandler',
                'getFormClassName',
                'getFormInstance'
            ],
            $formObject
        );

        $viewHelper->method('getFormInstance')
            ->willReturn(new DefaultForm);

        $viewHelper->initializeArguments();
        $viewHelper->initialize();
        $viewHelper->injectFormService($formService);

        $tagBuilder = $this->getMockBuilder(TagBuilder::class)
            ->setMethods(['addAttributes'])
            ->getMock();
        $this->inject($viewHelper, 'tag', $tagBuilder);

        $tagBuilder->expects($this->exactly(1))
            ->method('addAttributes')
            ->with($mergedDataAttributes);

        $viewHelper->expects($this->once())
            ->method('getDataAttributesAssetHandler')
            ->willReturnCallback(function () use ($fieldsValuesDataAttributes, $fieldsValidDataAttributes, $fieldsMessagesDataAttributes) {
                $assetHandlerMock = $this->getMockBuilder(DataAttributesAssetHandler::class)
                    ->disableOriginalConstructor()
                    ->setMethods(['getFieldsValuesDataAttributes', 'getFieldsValidDataAttributes', 'getFieldsMessagesDataAttributes'])
                    ->getMock();

                $assetHandlerMock->expects($this->once())
                    ->method('getFieldsValuesDataAttributes')
                    ->willReturn($fieldsValuesDataAttributes);

                $assetHandlerMock->expects($this->once())
                    ->method('getFieldsValidDataAttributes')
                    ->willReturn($fieldsValidDataAttributes);

                $assetHandlerMock->expects($this->once())
                    ->method('getFieldsMessagesDataAttributes')
                    ->willReturn($fieldsMessagesDataAttributes);

                return $assetHandlerMock;
            });

        $viewHelper->render();
    }

    /**
     * If a form instance if given to the `FormObject` (mostly with the `object`
     * argument of the `FormViewHelper`), and if there is no validation result
     * for the form, a temporary result is fetched to be given to the data
     * attributes asset handler managing the fields values.
     *
     * @test
     */
    public function temporaryFormValidationResultIsFetchedForFieldsValuesDataAttributes()
    {
        $formObject = $this->getDefaultFormObject();
        $formObject->setForm(new DefaultForm);

        $viewHelper = $this->getFormViewHelperMock(
            [
                'addDefaultClass',
                'handleAssets',
                'getDataAttributesAssetHandler',
                'getFormClassName',
                'getFormValidator',
                'getFormInstance'
            ],
            $formObject
        );

        $viewHelper->method('getFormInstance')
            ->willReturn(new DefaultForm);

        $viewHelper->initializeArguments();
        $viewHelper->initialize();

        $formResult = new FormResult;

        $viewHelper->expects($this->once())
            ->method('getFormValidator')
            ->willReturnCallback(function () use ($formResult) {
                $formValidation = $this->getMockBuilder(DefaultFormValidator::class)
                    ->disableOriginalConstructor()
                    ->setMethods(['validateGhost'])
                    ->getMock();

                $formValidation->expects($this->once())
                    ->method('validateGhost')
                    ->willReturn($formResult);

                return $formValidation;
            });

        $viewHelper->expects($this->once())
            ->method('getDataAttributesAssetHandler')
            ->willReturnCallback(function () use ($formResult) {
                $assetHandlerMock = $this->getMockBuilder(DataAttributesAssetHandler::class)
                    ->disableOriginalConstructor()
                    ->setMethods(['getFieldsValuesDataAttributes'])
                    ->getMock();

                $assetHandlerMock->expects($this->once())
                    ->method('getFieldsValuesDataAttributes')
                    ->with($formResult)
                    ->willReturn([]);

                return $assetHandlerMock;
            });

        $viewHelper->render();
    }

    /**
     * If the given form class name is not found, an exception must be thrown.
     *
     * @test
     */
    public function formClassNameNotFoundThrowsException()
    {
        $this->setExpectedException(ClassNotFoundException::class);

        $viewHelper = $this->getFormViewHelperMock();
        $viewHelper->initializeArguments();
        $viewHelper->setArguments(['formClassName' => 'not existing class']);
        $viewHelper->initialize();
    }

    /**
     * If the given form class name does not match the required conditions, an
     * exception must be thrown.
     *
     * @test
     */
    public function invalidFormClassNameThrowsException()
    {
        $this->setExpectedException(InvalidOptionValueException::class);

        $viewHelper = $this->getFormViewHelperMock();
        $viewHelper->initializeArguments();
        $viewHelper->setArguments(['formClassName' => \stdClass::class]);
        $viewHelper->initialize();
    }

    /**
     * If the argument `formClassName` is not given, the form class name must be
     * fetched by using the controller action.
     *
     * @test
     */
    public function formClassNameIsFetchedFromControllerIfNotGivenInArguments()
    {
        $viewHelper = $this->getFormViewHelperMock(['getFormClassNameFromControllerAction']);

        $viewHelper->expects($this->atLeastOnce())
            ->method('getFormClassNameFromControllerAction')
            ->willReturn(DefaultForm::class);

        $viewHelper->initialize();
    }

    /**
     * When the form configuration contains error, a block of text must be
     * returned to explain to the user what are the errors.
     *
     * @test
     */
    public function errorHelpTextIsReturnedWhenConfigurationHasError()
    {
        $formObject = $this->getDefaultFormObject();
        $formObject->getDefinitionValidationResult()->addError(new Error('foo', 42));

        $viewHelper = $this->getFormViewHelperMock(
            [
                'renderForm',
                'getFormClassName',
                'getErrorText',
                'getFormInstance'
            ],
            $formObject
        );

        $viewHelper->method('getFormInstance')
            ->willReturn(new DefaultForm);

        $viewHelper->expects($this->once())
            ->method('getErrorText');

        $viewHelper->initializeArguments();
        $viewHelper->initialize();
        $viewHelper->render();
    }

    /**
     * @param array      $methods
     * @param FormObject $formObject
     * @return \PHPUnit_Framework_MockObject_MockObject|FormViewHelper
     */
    protected function getFormViewHelperMock(array $methods = [], FormObject $formObject = null)
    {
        $formObject = $formObject ?: $this->getDefaultFormObject();
        $defaultMethods = ['getParentRenderResult', 'getFormObject'];
        $methods = array_merge($defaultMethods, $methods);

        /** @var FormViewHelper|\PHPUnit_Framework_MockObject_MockObject $viewHelper */
        $viewHelper = $this->getMockBuilder($this->getFormViewHelperClassName())
            ->setMethods($methods)
            ->getMock();

        $viewHelper->method('getFormObject')
            ->willReturn($formObject);

        $viewHelper->setRenderingContext($this->renderingContext);
        $tagBuilder = $this->getMockBuilder(TagBuilder::class)->getMock();
        $this->inject($viewHelper, 'tag', $tagBuilder);

        $viewHelper->injectFormService(new FormViewHelperService);
        $viewHelper->injectControllerService(Core::instantiate(ControllerService::class));

        return $viewHelper;
    }

    /**
     * @return string
     */
    protected function getFormViewHelperClassName()
    {
        return (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '8.3.0', '<'))
            ? OldFormViewHelper::class
            : FormViewHelper::class;
    }
}
