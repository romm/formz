<?php
namespace Romm\Formz\Tests\Unit\ViewHelpers;

use Romm\Formz\Error\Error;
use Romm\Formz\Error\Notice;
use Romm\Formz\Error\Warning;
use Romm\Formz\Form\FormObjectFactory;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\ViewHelpers\FormatMessageViewHelper;
use Romm\Formz\ViewHelpers\Service\FormzViewHelperService;
use TYPO3\CMS\Extbase\Error\Message;

class FormatMessageViewHelperTest extends AbstractViewHelperUnitTest
{
    /**
     * @param Message $message
     * @param string  $expected
     * @param null    $messageTemplate
     *
     * @test
     * @dataProvider renderViewHelperDataProvider
     */
    public function renderViewHelper(Message $message, $expected, $messageTemplate = null)
    {
        $viewHelper = new FormatMessageViewHelper;
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->initializeArguments();

        $viewHelper->setArguments(
            [
                'message' => $message,
                'field'   => 'foo'
            ]
        );

        $service = $this->getService();
        $viewHelper->injectFormzViewHelperService($service);

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
                'expected'        => 'foo - formz-foo-foo - error - bar - baz - foo',
                'messageTemplate' => '#FIELD# - #FIELD_ID# - #TYPE# - #VALIDATOR# - #KEY# - #MESSAGE#'
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
        $formObject = $formObjectFactory->getInstanceFromClassName(DefaultForm::class, 'foo');

        /** @noinspection PhpUndefinedMethodInspection */
        $service->method('getFormObject')
            ->willReturn($formObject);

        return $service;
    }
}
