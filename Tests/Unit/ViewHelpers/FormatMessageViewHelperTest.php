<?php
namespace Romm\Formz\Tests\Unit\ViewHelpers;

use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Error\Error;
use Romm\Formz\Error\Notice;
use Romm\Formz\Error\Warning;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Exceptions\InvalidArgumentTypeException;
use Romm\Formz\Exceptions\InvalidEntryException;
use Romm\Formz\Form\FormObjectFactory;
use Romm\Formz\Tests\Fixture\Form\ExtendedForm;
use Romm\Formz\ViewHelpers\FormatMessageViewHelper;
use Romm\Formz\ViewHelpers\Service\FormzViewHelperService;
use TYPO3\CMS\Extbase\Error\Message;

class FormatMessageViewHelperTest extends AbstractViewHelperUnitTest
{
    /**
     * @test
     * @dataProvider renderViewHelperDataProvider
     *
     * @param Message $message
     * @param string  $expected
     * @param string  $messageTemplate
     * @param string  $expectedException
     * @param Field   $field
     */
    public function renderViewHelper($message, $expected, $messageTemplate = null, $expectedException = null, Field $field = null)
    {
        if (null !== $expectedException) {
            $this->setExpectedException($expectedException);
        }

        $viewHelper = new FormatMessageViewHelper;
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->initializeArguments();

        $viewHelper->setArguments(
            [
                'message' => $message,
                'field'   => $field ? '' : 'foo'
            ]
        );

        $service = $this->getService();
        $viewHelper->injectFormzViewHelperService($service);

        if (null !== $field) {
            $service->setCurrentField($field);
        }

        if (null !== $messageTemplate) {
            $service->getFormObject()
                ->getConfiguration()
                ->getField('foo')
                ->getSettings()
                ->setMessageTemplate($messageTemplate);
        }

        $result = $viewHelper->render();

        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for function `renderViewHelper()`.
     *
     * @return array
     */
    public function renderViewHelperDataProvider()
    {
        $barField = new Field;
        $barField->setFieldName('bar');

        $bazField = new Field;
        $bazField->setFieldName('baz');

        return [
            [
                'message'  => new Error('foo', 1337, 'bar', 'baz'),
                'expected' => '<span class="js-validation-rule-bar js-validation-type-error js-validation-message-baz">foo</span>'
            ],
            [
                'message'  => new Warning('foo', 1337, 'bar', 'baz'),
                'expected' => '<span class="js-validation-rule-bar js-validation-type-warning js-validation-message-baz">foo</span>'
            ],
            [
                'message'  => new Notice('foo', 1337, 'bar', 'baz'),
                'expected' => '<span class="js-validation-rule-bar js-validation-type-notice js-validation-message-baz">foo</span>'
            ],
            [
                'message'  => new Message('foo', 1337),
                'expected' => '<span class="js-validation-rule-unknown js-validation-type-message js-validation-message-unknown">foo</span>'
            ],
            [
                'message'         => new Error('foo', 1337, 'bar', 'baz'),
                'expected'        => 'foo - formz-foo-foo - error - bar - baz - foo',
                'messageTemplate' => '#FIELD# - #FIELD_ID# - #TYPE# - #VALIDATOR# - #KEY# - #MESSAGE#'
            ],
            [
                'message'           => new \stdClass,
                'expected'          => null,
                'messageTemplate'   => null,
                'expectedException' => InvalidArgumentTypeException::class
            ],
            [
                'message'           => new Error('foo', 1337, 'bar', 'baz'),
                'expected'          => '<span class="js-validation-rule-bar js-validation-type-error js-validation-message-baz">foo</span>',
                'messageTemplate'   => null,
                'expectedException' => null,
                'field'             => $barField
            ],
            [
                'message'           => new Error('foo', 1337, 'bar', 'baz'),
                'expected'          => null,
                'messageTemplate'   => null,
                'expectedException' => EntryNotFoundException::class,
                'field'             => $bazField
            ],
            [
                'message'           => new Error('foo', 1337, 'bar', 'baz'),
                'expected'          => null,
                'messageTemplate'   => null,
                'expectedException' => InvalidEntryException::class,
                'field'             => new Field
            ]
        ];
    }

    /**
     * @return FormzViewHelperService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getService()
    {
        $service = $this->getMock(FormzViewHelperService::class, ['getFormObject']);
        $formObjectFactory = new FormObjectFactory;
        $formObject = $formObjectFactory->getInstanceFromClassName(ExtendedForm::class, 'foo');

        /** @noinspection PhpUndefinedMethodInspection */
        $service->method('getFormObject')
            ->willReturn($formObject);

        return $service;
    }
}
