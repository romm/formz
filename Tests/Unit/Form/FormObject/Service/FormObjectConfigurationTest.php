<?php

namespace Romm\Formz\Tests\Unit\Form\FormObject\Service;

use Romm\ConfigurationObject\ConfigurationObjectInstance;
use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Form\Definition\FormDefinition;
use Romm\Formz\Form\FormObject\Definition\FormDefinitionObject;
use Romm\Formz\Form\FormObject\Service\FormObjectConfiguration;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Result;

class FormObjectConfigurationTest extends AbstractUnitTest
{
    /**
     * The configuration validation result should be calculated once.
     *
     * @test
     */
    public function configurationValidationResultIsCalculatedOnce()
    {
        $formObjectConfiguration = $this->getFormObjectConfigurationMock(['getMergedValidationResult']);

        $formObjectConfiguration->expects($this->once())
            ->method('getMergedValidationResult')
            ->willReturn(new Result);

        for ($i = 0; $i < 3; $i++) {
            $formObjectConfiguration->getConfigurationValidationResult();
        }

        unset($formObjectConfiguration);
    }

    /**
     * Checks that the errors from the list below are merged and can be
     * retrieved:
     * - the global configuration validation;
     * - the form configuration mapping validation;
     *
     * @test
     */
    public function configurationValidationErrorsAreReturned()
    {
        $formDefinitionObjectMock = $this->getMockBuilder(FormDefinitionObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValidationResult'])
            ->getMock();

        $formObjectConfiguration = $this->getFormObjectConfigurationMock(
            ['getGlobalConfigurationValidationResult', 'getFormDefinitionValidationResult'],
            $formDefinitionObjectMock
        );

        $globalConfigurationValidationResult = new Result;
        $globalConfigurationValidationResult->forProperty('foo')->addError(new Error('foo', 42));

        $formMappingValidationResult = new Result;
        $formMappingValidationResult->forProperty('bar')->addError(new Error('bar', 1337));

        $formDefinitionObjectMock->method('getValidationResult')
            ->willReturn($formMappingValidationResult);

        $formObjectConfiguration->expects($this->once())
            ->method('getGlobalConfigurationValidationResult')
            ->willReturn($globalConfigurationValidationResult);

        $formObjectConfiguration->expects($this->never())
            ->method('getFormDefinitionValidationResult');

        $result = $formObjectConfiguration->getConfigurationValidationResult();

        $this->assertInstanceOf(Result::class, $result);

        $fooResult = $result->forProperty('foo');
        $this->assertTrue($fooResult->hasErrors());
        $this->assertEquals(42, $fooResult->getFirstError()->getCode());

        $barResult = $result->forProperty('forms.' . DefaultForm::class . '.bar');
        $this->assertTrue($barResult->hasErrors());
        $this->assertEquals(1337, $barResult->getFirstError()->getCode());
    }

    /**
     * The form definition validation, which is handled by its own validator,
     * must be returned only if the root configuration *and* the definition
     * basic validation have no errors.
     *
     * @test
     */
    public function formDefinitionValidationResultAreReturned()
    {
        $formDefinitionObjectMock = $this->getMockBuilder(FormDefinitionObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValidationResult'])
            ->getMock();

        $formObjectConfiguration = $this->getFormObjectConfigurationMock(
            ['getGlobalConfigurationValidationResult', 'getFormDefinitionValidationResult'],
            $formDefinitionObjectMock
        );

        $formDefinitionValidationResult = new Result;
        $formDefinitionValidationResult->forProperty('baz')->addError(new Error('baz', 404));

        $formDefinitionObjectMock->method('getValidationResult')
            ->willReturn(new Result);

        $formObjectConfiguration->expects($this->once())
            ->method('getGlobalConfigurationValidationResult')
            ->willReturn(new Result);

        $formObjectConfiguration->expects($this->once())
            ->method('getFormDefinitionValidationResult')
            ->willReturn($formDefinitionValidationResult);

        $result = $formObjectConfiguration->getConfigurationValidationResult();

        $this->assertInstanceOf(Result::class, $result);

        $bazResult = $result->forProperty('forms.' . DefaultForm::class . '.baz');
        $this->assertTrue($bazResult->hasErrors());
        $this->assertEquals(404, $bazResult->getFirstError()->getCode());
    }

    /**
     * @param array                     $methods
     * @param FormDefinitionObject|null $formDefinitionObject
     * @return \PHPUnit_Framework_MockObject_MockObject|FormObjectConfiguration
     */
    protected function getFormObjectConfigurationMock($methods = [], FormDefinitionObject $formDefinitionObject = null)
    {
        $formDefinitionObject = $formDefinitionObject ?: new FormDefinitionObject(new FormDefinition, new Result);

        /** @var FormObjectConfiguration|\PHPUnit_Framework_MockObject_MockObject $formObjectConfiguration */
        $formObjectConfiguration = $this->getMockBuilder(FormObjectConfiguration::class)
            ->setMethods($methods)
            ->setConstructorArgs([$this->getDefaultFormObjectStatic(), $formDefinitionObject])
            ->getMock();

        return $formObjectConfiguration;
    }

    /**
     * @return ConfigurationObjectInstance
     */
    protected function getConfigurationObjectInstance()
    {
        $formzConfiguration = new Configuration;
        $result = new Result;

        return new ConfigurationObjectInstance($formzConfiguration, $result);
    }
}
