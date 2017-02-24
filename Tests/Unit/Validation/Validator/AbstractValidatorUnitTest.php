<?php
namespace Romm\Formz\Tests\Unit\Validation\Validator;

use Romm\Formz\Configuration\Form\Field\Validation\Validation;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Validation\DataObject\ValidatorDataObject;
use Romm\Formz\Validation\Validator\AbstractValidator;

abstract class AbstractValidatorUnitTest extends AbstractUnitTest
{
    /**
     * @var string
     */
    protected $validatorClassName;

    /**
     * @param string $className
     * @param array  $options
     * @param array  $methods
     * @return \PHPUnit_Framework_MockObject_MockObject|AbstractValidator
     */
    protected function getValidatorInstance($className, array $options = [], array $methods = [])
    {
        $formMock = $this->getMockBuilder(FormInterface::class)
            ->getMock();

        $validation = new Validation;
        $validation->setArrayIndex('foo');

        $validatorDataObject = new ValidatorDataObject($formMock, $validation);

        return (empty($methods))
            ? new $className($options, $validatorDataObject)
            : $this->getMockBuilder($className)
                ->setConstructorArgs([$options, $validatorDataObject])
                ->setMethods($methods)
                ->getMock();
    }

    /**
     * @param string $value
     * @param array  $options
     * @param array  $errors
     * @param array  $warnings
     * @param array  $notices
     * @return \TYPO3\CMS\Extbase\Error\Result
     */
    protected function validateValidator($value, array $options, array $errors = [], array $warnings = [], array $notices = [])
    {
        $validator = $this->getValidatorInstance(
            $this->validatorClassName,
            $options,
            ['addError', 'addWarning', 'addNotice']
        );

        $messages = [
            [$errors, 'addError'],
            [$warnings, 'addWarning'],
            [$notices, 'addNotice']
        ];

        foreach ($messages as $message) {
            list($messagesList, $messageMethod) = $message;

            if (empty($messagesList)) {
                $validator->expects($this->never())
                    ->method($messageMethod);
            } else {
                $i = 0;

                foreach ($messagesList as $messageValue) {
                    $validator->expects($this->at($i++))
                        ->method($messageMethod)
                        ->with($messageValue);
                }
            }
        }

        return $validator->validate($value);
    }
}
