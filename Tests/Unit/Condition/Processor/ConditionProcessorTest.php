<?php

namespace Romm\Formz\Tests\Unit\Condition\Processor;

use Prophecy\Argument;
use Prophecy\Argument\Token\TokenInterface;
use Prophecy\Prophecy\ObjectProphecy;
use Romm\Formz\Condition\Items\FieldHasValueCondition;
use Romm\Formz\Condition\Parser\ConditionParserFactory;
use Romm\Formz\Condition\Parser\Node\ConditionNode;
use Romm\Formz\Condition\Parser\Tree\ConditionTree;
use Romm\Formz\Condition\Processor\ConditionProcessor;
use Romm\Formz\Form\Definition\Condition\ActivationInterface;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\Definition\FormDefinition;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use Romm\Formz\Tests\Unit\UnitTestContainer;
use Romm\Formz\Validation\Validator\RequiredValidator;
use TYPO3\CMS\Extbase\Error\Result;

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
        $conditionProcessor = new ConditionProcessor($this->getDefaultFormObject());

        $field1 = new Field('foo');
        $field1->addActivation();
        $field2 = new Field('bar');
        $field2->addActivation();

        $conditionParserFactoryProphecy = $this->getConditionParserProphecy(
            Argument::that(function ($activation) use ($field1, $field2) {
                return $activation === $field1->getActivation()
                    || $activation === $field2->getActivation();
            }),
            $conditionProcessor
        );

        UnitTestContainer::get()->registerMockedInstance(ConditionParserFactory::class, $conditionParserFactoryProphecy->reveal());

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
        $conditionProcessor = new ConditionProcessor($this->getDefaultFormObject());

        $field = new Field('foo');

        $validation1 = $field->addValidator('foo', RequiredValidator::class);
        $validation1->addActivation();
        $validation2 = $field->addValidator('bar', RequiredValidator::class);
        $validation2->addActivation();

        $conditionParserFactoryProphecy = $this->getConditionParserProphecy(
            Argument::that(function ($activation) use ($validation1, $validation2) {
                return $activation === $validation1->getActivation()
                    || $activation === $validation2->getActivation();
            }),
            $conditionProcessor
        );

        UnitTestContainer::get()->registerMockedInstance(ConditionParserFactory::class, $conditionParserFactoryProphecy->reveal());

        $tree1 = $conditionProcessor->getActivationConditionTreeForValidator($validation1);
        $tree2 = $conditionProcessor->getActivationConditionTreeForValidator($validation1);
        $tree3 = $conditionProcessor->getActivationConditionTreeForValidator($validation2);

        $this->assertInstanceOf(ConditionTree::class, $tree1);
        $this->assertSame($tree1, $tree2);
        $this->assertNotSame($tree1, $tree3);
    }

    /**
     * Checks that calculating the whole tree will call the correct methods.
     *
     * @test
     */
    public function calculateAllTreesFetchesEverything()
    {
        $formObject = $this->getMockBuilder(FormObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefinition'])
            ->getMock();

        /** @var ConditionProcessor|\PHPUnit_Framework_MockObject_MockObject $conditionProcessor */
        $conditionProcessor = $this->getMockBuilder(ConditionProcessor::class)
            ->setMethods(['getActivationConditionTreeForField', 'getActivationConditionTreeForValidator'])
            ->setConstructorArgs([$formObject])
            ->getMock();

        $conditionProcessor->expects($this->exactly(2))
            ->method('getActivationConditionTreeForField');

        $conditionProcessor->expects($this->exactly(4))
            ->method('getActivationConditionTreeForValidator');

        $formObject->expects($this->once())
            ->method('getDefinition')
            ->willReturnCallback(function () use ($conditionProcessor) {
                $formConfiguration = $this->getMockBuilder(FormDefinition::class)
                    ->setMethods(['getFields'])
                    ->getMock();

                $formConfiguration->expects($this->once())
                    ->method('getFields')
                    ->willReturnCallback(function () use ($conditionProcessor) {
                        $field1 = new Field('foo');
                        $field1->addValidator('foo', RequiredValidator::class);
                        $field1->addValidator('bar', RequiredValidator::class);

                        $field2 = new Field('bar');
                        $field2->addValidator('foo', RequiredValidator::class);
                        $field2->addValidator('bar', RequiredValidator::class);

                        return [$field1, $field2];
                    });

                return $formConfiguration;
            });

        $conditionProcessor->calculateAllTrees();
    }

    /**
     * Checks that JavaScript files from condition items are collected.
     *
     * @test
     */
    public function conditionJavaScriptFilesAreCollected()
    {
        $condition = new FieldHasValueCondition('foo', 'bar');
        $conditionNode = new ConditionNode('foo', $condition);
        $tree = new ConditionTree($conditionNode, new Result);

        /** @var ConditionProcessor|\PHPUnit_Framework_MockObject_MockObject $conditionProcessor */
        $conditionProcessor = $this->getMockBuilder(ConditionProcessor::class)
            ->setMethods(['getNewConditionTreeFromActivation'])
            ->setConstructorArgs([$this->getDefaultFormObject()])
            ->getMock();

        $conditionProcessor->expects($this->once())
            ->method('getNewConditionTreeFromActivation')
            ->willReturn($tree);

        $field = new Field('bar');
        $field->addActivation();

        $conditionProcessor->getActivationConditionTreeForField($field);

        $this->assertEquals(
            $condition->getJavaScriptFiles(),
            $conditionProcessor->getJavaScriptFiles()
        );
    }

    /**
     * @param TokenInterface|ActivationInterface $activations
     * @param ConditionProcessor                 $conditionProcessor
     * @return ObjectProphecy|ConditionParserFactory
     */
    protected function getConditionParserProphecy(TokenInterface $activations, ConditionProcessor $conditionProcessor)
    {
        /** @var ConditionParserFactory|ObjectProphecy $conditionParserFactoryProphecy */
        $conditionParserFactoryProphecy = $this->prophesize(ConditionParserFactory::class);

        $getConditionTreeProphecy = function () {
            /** @var ConditionTree|ObjectProphecy $conditionTreeProphecy */
            $conditionTreeProphecy = $this->prophesize(ConditionTree::class);

            return $conditionTreeProphecy;
        };

        $conditionParserFactoryProphecy->parse($activations)
            ->shouldBeCalled()
            ->will(function ($arguments) use ($getConditionTreeProphecy, $conditionProcessor) {
                $conditionTreeProphecy = $getConditionTreeProphecy();

                $conditionTreeProphecy->injectDependencies($conditionProcessor, $arguments[0])
                    ->shouldBeCalled()
                    ->willReturn($conditionTreeProphecy);

                $conditionTreeProphecy->alongNodes(Argument::type('callable'))
                    ->shouldBeCalled();

                return $conditionTreeProphecy->reveal();
            });

        return $conditionParserFactoryProphecy;
    }
}
