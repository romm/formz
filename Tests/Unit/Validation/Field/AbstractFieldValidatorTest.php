<?php
namespace Romm\Formz\Tests\Unit\Validation\Field;

use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Form\Definition\Field\Validation\Validator;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Fixture\Validation\Validator\DummyFieldValidator;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Validation\Field\DataObject\ValidatorDataObject;
use TYPO3\CMS\Extbase\Error\Result;

class AbstractFieldValidatorTest extends AbstractUnitTest
{
    /**
     * @test
     * @dataProvider runValidatorDataProvider
     * @param mixed    $value
     * @param array    $messages
     * @param callable $callback
     * @param callable $finalCallback
     * @param string   $expectedException
     */
    public function runValidator($value, array $messages, $callback, $finalCallback, $expectedException = null)
    {
        if (null !== $expectedException) {
            $this->setExpectedException($expectedException);
        }

        $validator = new Validator('foo', DummyFieldValidator::class);

        foreach ($messages as $message) {
            $validator->addMessage($message[0])->setValue($message[1]);
        }

        $formObject = $this->getDefaultFormObject();
        $formObject->setForm(new DefaultForm);

        $validatorDataObject = new ValidatorDataObject($formObject, $validator);

        $validator = new DummyFieldValidator([], $validatorDataObject);
        if (is_callable($callback)) {
            $validator->setCallBack($callback);
        }

        $result = $validator->validate($value);
        if (is_callable($finalCallback)) {
            call_user_func($finalCallback, $result, $validator);
        }
    }

    /**
     * @return array
     */
    public function runValidatorDataProvider()
    {
        return [
            /*
             * #1
             *
             * Classical test: the validator will do nothing, the result will be
             * empty.
             */
            [
                'value'    => 'foo',
                'messages' => [],
                'callback' => null,
                'final'    => function (Result $result) {
                    $this->assertFalse($result->hasErrors());
                    $this->assertFalse($result->hasWarnings());
                    $this->assertFalse($result->hasNotices());
                }
            ],
            /*
             * #2
             *
             * Adding an unknown message key must throw an exception.
             */
            [
                'value'     => 'foo',
                'messages'  => [],
                'callback'  => function (DummyFieldValidator $validator) {
                    $validator->addNewError('unknownMessage', 42, [], '');
                },
                'final'     => null,
                'exception' => EntryNotFoundException::class
            ],
            /*
             * #3
             *
             * Adding an error with the validator should add an error to the
             * result, after it has been converted using the proper messages.
             */
            [
                'value'    => 'foo',
                'messages' => [],
                'callback' => function (DummyFieldValidator $validator) {
                    $validator->addNewError(DummyFieldValidator::MESSAGE_1, 42, ['bar'], 'baz');
                },
                'final'    => function (Result $result) {
                    $this->assertTrue($result->hasErrors());
                    $message = $result->getFirstError();
                    $this->assertEquals('message: bar', $message->getMessage());
                    $this->assertEquals(42, $message->getCode());
                    $this->assertEquals('baz', $message->getTitle());
                }
            ],
            /*
             * #4
             *
             * Adding an error using a key that was overridden should add that
             * error to the result with the correct message.
             */
            [
                'value'    => 'foo',
                'messages' => [[DummyFieldValidator::MESSAGE_1, 'hello world!']],
                'callback' => function (DummyFieldValidator $validator) {
                    $validator->addNewError(DummyFieldValidator::MESSAGE_1, 42, ['bar'], 'baz');
                },
                'final'    => function (Result $result) {
                    $this->assertTrue($result->hasErrors());
                    $message = $result->getFirstError();
                    $this->assertEquals('hello world!', $message->getMessage());
                    $this->assertEquals(42, $message->getCode());
                    $this->assertEquals('baz', $message->getTitle());
                }
            ],
            /*
             * #5
             *
             * Same as #3 but with a warning.
             */
            [
                'value'    => 'foo',
                'messages' => [],
                'callback' => function (DummyFieldValidator $validator) {
                    $validator->addNewWarning(DummyFieldValidator::MESSAGE_1, 42, ['bar'], 'baz');
                },
                'final'    => function (Result $result) {
                    $this->assertTrue($result->hasWarnings());
                    $message = $result->getFirstWarning();
                    $this->assertEquals('message: bar', $message->getMessage());
                    $this->assertEquals(42, $message->getCode());
                    $this->assertEquals('baz', $message->getTitle());
                }
            ],
            /*
             * #6
             *
             * Same as #4 but with a warning.
             */
            [
                'value'    => 'foo',
                'messages' => [[DummyFieldValidator::MESSAGE_1, 'hello world!']],
                'callback' => function (DummyFieldValidator $validator) {
                    $validator->addNewWarning(DummyFieldValidator::MESSAGE_1, 42, ['bar'], 'baz');
                },
                'final'    => function (Result $result) {
                    $this->assertTrue($result->hasWarnings());
                    $message = $result->getFirstWarning();
                    $this->assertEquals('hello world!', $message->getMessage());
                    $this->assertEquals(42, $message->getCode());
                    $this->assertEquals('baz', $message->getTitle());
                }
            ],
            /*
             * #7
             *
             * Same as #3 but with a notice.
             */
            [
                'value'    => 'foo',
                'messages' => [],
                'callback' => function (DummyFieldValidator $validator) {
                    $validator->addNewNotice(DummyFieldValidator::MESSAGE_1, 42, ['bar'], 'baz');
                },
                'final'    => function (Result $result) {
                    $this->assertTrue($result->hasNotices());
                    $message = $result->getFirstNotice();
                    $this->assertEquals('message: bar', $message->getMessage());
                    $this->assertEquals(42, $message->getCode());
                    $this->assertEquals('baz', $message->getTitle());
                }
            ],
            /*
             * #8
             *
             * Same as #4 but with a notice.
             */
            [
                'value'    => 'foo',
                'messages' => [[DummyFieldValidator::MESSAGE_1, 'hello world!']],
                'callback' => function (DummyFieldValidator $validator) {
                    $validator->addNewNotice(DummyFieldValidator::MESSAGE_1, 42, ['bar'], 'baz');
                },
                'final'    => function (Result $result) {
                    $this->assertTrue($result->hasNotices());
                    $message = $result->getFirstNotice();
                    $this->assertEquals('hello world!', $message->getMessage());
                    $this->assertEquals(42, $message->getCode());
                    $this->assertEquals('baz', $message->getTitle());
                }
            ]
        ];
    }

    /**
     * @test
     */
    public function javaScriptValidationFilesCanBeAccessed()
    {
        $this->assertSame(DummyFieldValidator::$javaScriptValidationFiles, DummyFieldValidator::getJavaScriptValidationFiles());
    }
}
