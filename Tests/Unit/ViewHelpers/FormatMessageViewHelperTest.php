<?php
namespace Romm\Formz\Tests\Unit\ViewHelpers;

use Romm\Formz\Error\Error;
use Romm\Formz\Error\Notice;
use Romm\Formz\Error\Warning;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Exceptions\InvalidArgumentTypeException;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Service\ViewHelper\Field\FieldViewHelperService;
use Romm\Formz\Service\ViewHelper\Form\FormViewHelperService;
use Romm\Formz\ViewHelpers\FormatMessageViewHelper;
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

        $formService = $this->getFormService();
        $fieldService = new FieldViewHelperService;

        $viewHelper->injectFormService($formService);
        $viewHelper->injectFieldService($fieldService);

        if (null !== $field) {
            $fieldService->setCurrentField($field);
        }

        if (null !== $messageTemplate) {
            $formService->getFormObject()
                ->getDefinition()
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
        $barField = new Field('bar');
        $bazField = new Field('baz');

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
                'message'         => new Error('foo', 1337, 'bar', 'baz'),
                'expected'        => 'foo - fz-foo-foo - error - bar - baz - foo',
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
                'expectedException' => EntryNotFoundException::class,
                'field'             => new Field('hello')
            ]
        ];
    }

    /**
     * @return FormViewHelperService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFormService()
    {
        $service = $this->getMockBuilder(FormViewHelperService::class)
            ->setMethods(['getFormObject'])
            ->getMock();

        /** @noinspection PhpUndefinedMethodInspection */
        $service->method('getFormObject')
            ->willReturn($this->getExtendedFormObject());

        return $service;
    }
}
