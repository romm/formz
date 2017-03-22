<?php
namespace Romm\Formz\Tests\Unit\Service;

use Romm\Formz\Error\Error as FormzError;
use Romm\Formz\Error\FormzMessageInterface;
use Romm\Formz\Error\Notice as FormzNotice;
use Romm\Formz\Error\Warning as FormzWarning;
use Romm\Formz\Service\MessageService;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Message;
use TYPO3\CMS\Extbase\Error\Notice;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Error\Warning;

class MessageServiceTest extends AbstractUnitTest
{
    /**
     * Checks that each instance of Extbase errors, warnings and notices are
     * replaced by FormZ instances with correct values.
     *
     * @test
     */
    public function sanitizeValidatorResult()
    {
        $result = new Result;

        $error1 = new Error('error1', 10);
        $result->addError($error1);

        $error2 = new FormzError('error2', 11, 'bar', 'baz');
        $result->addError($error2);

        $warning1 = new Warning('warning1', 20);
        $result->addWarning($warning1);

        $warning2 = new FormzWarning('warning2', 21, 'bar', 'baz');
        $result->addWarning($warning2);

        $notice1 = new Notice('notice1', 30);
        $result->addNotice($notice1);

        $notice2 = new FormzNotice('notice2', 31, 'bar', 'baz');
        $result->addNotice($notice2);

        $service = new MessageService;
        $sanitizedResult = $service->sanitizeValidatorResult($result, 'foo');

        $errors = $sanitizedResult->getErrors();
        $warnings = $sanitizedResult->getWarnings();
        $notices = $sanitizedResult->getNotices();

        $messages = [
            [
                'item'           => reset($errors),
                'message'        => 'error1',
                'code'           => 10,
                'validationName' => 'foo',
                'messageKey'     => 'unknown-1'
            ],
            [
                'item'           => next($errors),
                'message'        => 'error2',
                'code'           => 11,
                'validationName' => 'bar',
                'messageKey'     => 'baz'
            ],
            [
                'item'           => reset($warnings),
                'message'        => 'warning1',
                'code'           => 20,
                'validationName' => 'foo',
                'messageKey'     => 'unknown-1'
            ],
            [
                'item'           => next($warnings),
                'message'        => 'warning2',
                'code'           => 21,
                'validationName' => 'bar',
                'messageKey'     => 'baz'
            ],
            [
                'item'           => reset($notices),
                'message'        => 'notice1',
                'code'           => 30,
                'validationName' => 'foo',
                'messageKey'     => 'unknown-1'
            ],
            [
                'item'           => next($notices),
                'message'        => 'notice2',
                'code'           => 31,
                'validationName' => 'bar',
                'messageKey'     => 'baz'
            ]
        ];

        foreach ($messages as $message) {
            /** @var FormzMessageInterface|Message $item */
            $item = $message['item'];

            $this->assertInstanceOf(FormzMessageInterface::class, $item);
            $this->assertEquals($message['message'], $item->getMessage());
            $this->assertEquals($message['code'], $item->getCode());
            $this->assertEquals($message['validationName'], $item->getValidationName());
            $this->assertEquals($message['messageKey'], $item->getMessageKey());
        }
    }
}
