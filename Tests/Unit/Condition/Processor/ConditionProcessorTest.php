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
use Romm\Formz\Configuration\Form\Condition\Activation\EmptyActivation;
use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Configuration\Form\Field\Validation\Validation;
use Romm\Formz\Configuration\Form\Form;
use Romm\Formz\Form\FormObject;
use Romm\Formz\Service\FacadeService;
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
                        $validation2 = new Validation;

                        $field1 = new Field;
                        $field1->setFieldName('foo');
                        $field1->addValidation('foo', $validation1);
                        $field1->addValidation('bar', $validation2);

                        $field2 = new Field;
                        $field2->setFieldName('bar');
                        $field2->addValidation('foo', $validation1);
                        $field2->addValidation('bar', $validation2);

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
            ->disableOriginalConstructor()
            ->getMock();

        $conditionProcessor->expects($this->once())
            ->method('getNewConditionTreeFromActivation')
            ->willReturn($tree);

        $field = new Field;
        $field->setFieldName('bar');
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

                $conditionTreeProphecy->alongNodes(Argument::type('callable'))
                    ->shouldBeCalled();

                return $conditionTreeProphecy->reveal();
            });

        return $conditionParserFactoryProphecy;
    }
}
