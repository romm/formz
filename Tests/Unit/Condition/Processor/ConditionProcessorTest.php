<?php
namespace Romm\Formz\Tests\Unit\Condition\Processor;

use Prophecy\Argument;
use Prophecy\Argument\Token\LogicalAndToken;
use Prophecy\Prophecy\ObjectProphecy;
use Romm\Formz\Condition\Parser\ConditionParserFactory;
use Romm\Formz\Condition\Parser\ConditionTree;
use Romm\Formz\Condition\Processor\ConditionProcessor;
use Romm\Formz\Configuration\Form\Condition\Activation\EmptyActivation;
use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Configuration\Form\Field\Validation\Validation;
use Romm\Formz\Service\FacadeService;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class ConditionProcessorTest extends AbstractUnitTest
{
    /**
     * Activation condition tree for a specific field should be stored in local
     * cache.
     *
     * @test
     */
    public function activationConditionTreeForFieldIsStoredInLocalCache()
    {
        $conditionProcessor = new ConditionProcessor($this->getFormObject());

        $field1 = new Field;
        $field1->setFieldName('foo');
        $field2 = new Field;
        $field2->setFieldName('bar');
        $this->inject($field2, 'activation', new EmptyActivation);

        $conditionParserFactoryProphecy = $this->getConditionParserProphecy(
            Argument::allOf($field1->getActivation(), $field2->getActivation()),
            $conditionProcessor
        );

        FacadeService::get()->forceInstance(ConditionParserFactory::class, $conditionParserFactoryProphecy->reveal());

        $tree1 = $conditionProcessor->getActivationConditionTreeForField($field1);
        $tree2 = $conditionProcessor->getActivationConditionTreeForField($field1);
        $tree3 = $conditionProcessor->getActivationConditionTreeForField($field2);

        $this->assertInstanceOf(ConditionTree::class, $tree1);
        $this->assertSame($tree1, $tree2);
        $this->assertNotSame($tree1, $tree3);
    }

    /**
     * Activation condition tree for a specific validation should be stored in
     * local cache.
     *
     * @test
     */
    public function activationConditionTreeForValidationIsStoredInLocalCache()
    {
        $conditionProcessor = new ConditionProcessor($this->getFormObject());

        $field = new Field;
        $field->setFieldName('foo');

        $validation1 = new Validation;
        $validation1->setValidationName('foo');
        $validation1->setParents([$field]);
        $validation2 = new Validation;
        $validation2->setValidationName('bar');
        $validation2->setParents([$field]);
        $this->inject($validation2, 'activation', new EmptyActivation);

        $conditionParserFactoryProphecy = $this->getConditionParserProphecy(
            Argument::allOf($validation1->getActivation(), $validation2->getActivation()),
            $conditionProcessor
        );

        FacadeService::get()->forceInstance(ConditionParserFactory::class, $conditionParserFactoryProphecy->reveal());

        $tree1 = $conditionProcessor->getActivationConditionTreeForValidation($validation1);
        $tree2 = $conditionProcessor->getActivationConditionTreeForValidation($validation1);
        $tree3 = $conditionProcessor->getActivationConditionTreeForValidation($validation2);

        $this->assertInstanceOf(ConditionTree::class, $tree1);
        $this->assertSame($tree1, $tree2);
        $this->assertNotSame($tree1, $tree3);
    }

    /**
     * @param LogicalAndToken    $activations
     * @param ConditionProcessor $conditionProcessor
     * @return ObjectProphecy|ConditionParserFactory
     */
    protected function getConditionParserProphecy(LogicalAndToken $activations, ConditionProcessor $conditionProcessor)
    {
        $prophet = $this->prophet;

        /** @var ConditionParserFactory|ObjectProphecy $conditionParserFactoryProphecy */
        $conditionParserFactoryProphecy = $this->prophet->prophesize(ConditionParserFactory::class);
        $conditionParserFactoryProphecy->parse($activations)
            ->shouldBeCalled()
            ->will(function () use ($prophet, $conditionProcessor) {
                /** @var ConditionTree|ObjectProphecy $conditionTreeProphecy */
                $conditionTreeProphecy = $prophet->prophesize(ConditionTree::class);

                $conditionTreeProphecy->attachConditionProcessor($conditionProcessor)
                    ->shouldBeCalled()
                    ->willReturn($conditionTreeProphecy);

                return $conditionTreeProphecy->reveal();
            });

        return $conditionParserFactoryProphecy;
    }
}
