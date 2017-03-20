<?php
namespace Romm\Formz\Tests\Unit\Condition\Processor;

use Prophecy\Argument;
use Prophecy\Argument\Token\TokenInterface;
use Prophecy\Prophecy\ObjectProphecy;
use Romm\Formz\Condition\Items\FieldHasValueCondition;
use Romm\Formz\Condition\Parser\ConditionParserFactory;
use Romm\Formz\Condition\Parser\ConditionTree;
use Romm\Formz\Condition\Parser\Node\ConditionNode;
use Romm\Formz\Condition\Processor\ConditionProcessor;
use Romm\Formz\Configuration\Form\Field\Activation\Activation;
use Romm\Formz\Configuration\Form\Field\Activation\EmptyActivation;
use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Configuration\Form\Field\Validation\Validation;
use Romm\Formz\Configuration\Form\Form;
use Romm\Formz\Form\FormObject;
use Romm\Formz\Service\InstanceService;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
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

        $field1 = new Field;
        $field1->setName('foo');
        $field2 = new Field;
        $field2->setName('bar');
        $field2->setActivation(new Activation);

        $conditionParserFactoryProphecy = $this->getConditionParserProphecy(
            Argument::that(function ($activation) use ($field1, $field2) {
                return $activation === $field1->getActivation()
                    || $activation === $field2->getActivation();
            }),
            $conditionProcessor
        );

        InstanceService::get()->forceInstance(ConditionParserFactory::class, $conditionParserFactoryProphecy->reveal());

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

        $field = new Field;
        $field->setName('foo');

        $validation1 = new Validation;
        $validation1->setName('foo');
        $validation1->setParents([$field]);
        $validation2 = new Validation;
        $validation2->setName('bar');
        $validation2->setParents([$field]);
        $this->inject($validation2, 'activation', new EmptyActivation);

        $conditionParserFactoryProphecy = $this->getConditionParserProphecy(
            Argument::that(function ($activation) use ($validation1, $validation2) {
                return $activation === $validation1->getActivation()
                    || $activation === $validation2->getActivation();
            }),
            $conditionProcessor
        );

        InstanceService::get()->forceInstance(ConditionParserFactory::class, $conditionParserFactoryProphecy->reveal());

        $tree1 = $conditionProcessor->getActivationConditionTreeForValidation($validation1);
        $tree2 = $conditionProcessor->getActivationConditionTreeForValidation($validation1);
        $tree3 = $conditionProcessor->getActivationConditionTreeForValidation($validation2);

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
            ->setMethods(['getConfiguration'])
            ->getMock();

        /** @var ConditionProcessor|\PHPUnit_Framework_MockObject_MockObject $conditionProcessor */
        $conditionProcessor = $this->getMockBuilder(ConditionProcessor::class)
            ->setMethods(['getActivationConditionTreeForField', 'getActivationConditionTreeForValidation'])
            ->setConstructorArgs([$formObject])
            ->getMock();

        $conditionProcessor->expects($this->exactly(2))
            ->method('getActivationConditionTreeForField');

        $conditionProcessor->expects($this->exactly(4))
            ->method('getActivationConditionTreeForValidation');

        $formObject->expects($this->once())
            ->method('getConfiguration')
            ->willReturnCallback(function () use ($conditionProcessor) {
                $formConfiguration = $this->getMockBuilder(Form::class)
                    ->setMethods(['getFields'])
                    ->getMock();

                $formConfiguration->expects($this->once())
                    ->method('getFields')
                    ->willReturnCallback(function () use ($conditionProcessor) {
                        $validation1 = new Validation;
                        $validation1->setName('foo');
                        $validation2 = new Validation;
                        $validation2->setName('bar');

                        $field1 = new Field;
                        $field1->setName('foo');
                        $field1->addValidation($validation1);
                        $field1->addValidation($validation2);

                        $field2 = new Field;
                        $field2->setName('bar');
                        $field2->addValidation($validation1);
                        $field2->addValidation($validation2);

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
        $condition = new FieldHasValueCondition;
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

        $field = new Field;
        $field->setName('bar');
        $this->inject($field, 'activation', new EmptyActivation);

        $conditionProcessor->getActivationConditionTreeForField($field);

        $this->assertEquals(
            $condition->getJavaScriptFiles(),
            $conditionProcessor->getJavaScriptFiles()
        );
    }

    /**
     * @param TokenInterface     $activations
     * @param ConditionProcessor $conditionProcessor
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
