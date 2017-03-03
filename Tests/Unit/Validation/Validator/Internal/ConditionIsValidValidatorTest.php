<?php
namespace Romm\Formz\Tests\Unit\Validation\Validator\Internal;

use Romm\Formz\Configuration\Form\Condition\Activation\AbstractActivation;
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
        $validator = new ConditionIsValidValidator;

        /** @var AbstractActivation|\PHPUnit_Framework_MockObject_MockObject $condition */
        $condition = $this->getMockBuilder(AbstractActivation::class)
            ->setMethods(['getExpression'])
            ->getMockForAbstractClass();

        $condition->method('getExpression')
            ->willReturn('invalid condition expression');

        $result = $validator->validate($condition);

        $this->assertTrue($result->hasErrors());
        $this->assertEquals(ConditionIsValidValidator::ERROR_CODE, $result->getFirstError()->getCode());
    }
}
