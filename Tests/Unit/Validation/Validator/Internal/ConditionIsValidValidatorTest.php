<?php
namespace Romm\Formz\Tests\Unit\Validation\Validator\Internal;

use Romm\Formz\Form\Definition\Condition\Activation;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Validation\Validator\Internal\ConditionIsValidValidator;

class ConditionIsValidValidatorTest extends AbstractUnitTest
{
    /**
     * Will test if the validator works correctly.
     *
     * @test
     */
    public function validatorWorks()
    {
        /** @var ConditionIsValidValidator $validator */
        $validator = $this->getMockBuilder(ConditionIsValidValidator::class)
            ->setMethods(['translateErrorMessage'])
            ->getMock();

        /** @var Activation|\PHPUnit_Framework_MockObject_MockObject $condition */
        $condition = $this->getMockBuilder(Activation::class)
            ->setMethods(['getExpression'])
            ->getMock();

        $condition->method('getExpression')
            ->willReturn('invalid condition expression');

        $result = $validator->validate($condition);

        $this->assertTrue($result->hasErrors());
        $this->assertEquals(ConditionIsValidValidator::ERROR_CODE, $result->getFirstError()->getCode());
    }
}
