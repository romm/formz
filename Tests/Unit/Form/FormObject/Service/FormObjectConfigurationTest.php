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
     * Checks that the errors from both the global configuration and the form
     * configuration are merged and can be retrieved.
     *
     * @test
     */
    public function configurationValidationErrorsAreReturned()
    {
        $formDefinitionObjectMock = $this->getMockBuilder(FormDefinitionObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValidationResult'])
            ->getMock();

        $formObjectConfiguration = $this->getFormObjectConfigurationMock(['getGlobalConfigurationValidationResult'], $formDefinitionObjectMock);

        $globalConfigurationValidationResult = new Result;
        $globalConfigurationValidationResult->forProperty('foo')->addError(new Error('foo', 42));

        $formConfigurationValidationResult = new Result;
        $formConfigurationValidationResult->forProperty('bar')->addError(new Error('bar', 1337));

        $formDefinitionObjectMock->method('getValidationResult')
            ->willReturn($formConfigurationValidationResult);

        $formObjectConfiguration->expects($this->once())
            ->method('getGlobalConfigurationValidationResult')
            ->willReturn($globalConfigurationValidationResult);

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
     * @param array                     $methods
     * @param FormDefinitionObject|null $formDefinitionObject
     * @return \PHPUnit_Framework_MockObject_MockObject|FormObjectConfiguration
     */
    protected function getFormObjectConfigurationMock($methods = [], FormDefinitionObject $formDefinitionObject = null)
    {
        $formDefinitionObject = $formDefinitionObject ?: new FormDefinitionObject(new FormDefinition);

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
