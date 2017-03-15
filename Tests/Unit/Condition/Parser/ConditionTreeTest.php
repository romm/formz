<?php
namespace Romm\Formz\Tests\Unit\Condition\Parser;

use Prophecy\Argument;
use Romm\Formz\Condition\Parser\ConditionParser;
use Romm\Formz\Condition\Parser\ConditionTree;
use Romm\Formz\Condition\Parser\Node\BooleanNode;
use Romm\Formz\Condition\Parser\Node\NodeInterface;
use Romm\Formz\Condition\Parser\Node\NullNode;
use Romm\Formz\Condition\Processor\ConditionProcessor;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use Romm\Formz\Configuration\Form\Condition\Activation\Activation;
use Romm\Formz\Tests\Fixture\Condition\Parser\Node\DependencyAwareNode;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Extbase\Error\Result;

class ConditionTreeTest extends AbstractUnitTest
{
    /**
     * Checks that the root node given to a new condition tree has the tree
     * assigned instantly.
     *
     * @test
     */
    public function constructorAddsTreeDependencyToRootNode()
    {
        $rootNodeProphecy = $this->prophesize();
        $rootNodeProphecy->willImplement(NodeInterface::class);

        $rootNodeProphecy->setTree(Argument::type(ConditionTree::class))
            ->shouldBeCalled();

        new ConditionTree($rootNodeProphecy->reveal(), new Result);
    }

    /**
     * Checks that the validation result given in the constructor is properly
     * injected and can be accessed with its getter function.
     *
     * @test
     */
    public function constructorInjectsValidationResult()
    {
        $result = new Result;
        $tree = new ConditionTree(new NullNode, $result);

        $this->assertSame($result, $tree->getValidationResult());
    }

    /**
     * Checks that the function `alongNodes()` will recurse on nodes.
     *
     * @test
     */
    public function nodesCanBeVisited()
    {
        $node = new NullNode;
        $tree = new ConditionTree($node, new Result);
        $flag = false;

        $tree->alongNodes(function (NodeInterface $thisNode) use ($node, &$flag) {
            $this->assertSame($thisNode, $node);
            $flag = true;
        });

        $this->assertTrue($flag);
    }

    /**
     * Checks that injecting dependencies in nodes that implement the interface
     * `ActivationDependencyAwareInterface` is done properly.
     *
     * @test
     */
    public function dependenciesAreInjectedInSubNodes()
    {
        $conditionProcessorProphecy = $this->prophesize(ConditionProcessor::class);
        $activationProphecy = $this->prophesize(Activation::class);

        $dependencyNode = $this->getMockBuilder(DependencyAwareNode::class)
            ->setMethods(['injectDependencies'])
            ->getMock();

        $dependencyNode->expects($this->once())
            ->method('injectDependencies')
            ->with($conditionProcessorProphecy->reveal(), $activationProphecy->reveal());

        $booleanNode = new BooleanNode(new NullNode, $dependencyNode, ConditionParser::LOGICAL_AND);

        $tree = new ConditionTree($booleanNode, new Result);

        $tree->injectDependencies($conditionProcessorProphecy->reveal(), $activationProphecy->reveal());
    }

    /**
     * @test
     */
    public function treeMethodsAreCalled()
    {
        /** @var NullNode|\PHPUnit_Framework_MockObject_MockObject $node */
        $node = $this->getMockBuilder(NullNode::class)
            ->setMethods(['getCssResult', 'getJavaScriptResult', 'getPhpResult'])
            ->getMock();

        $node->expects($this->once())
            ->method('getCssResult');

        $node->expects($this->once())
            ->method('getJavaScriptResult');

        /** @var PhpConditionDataObject $phpConditionDataObject */
        $phpConditionDataObject = $this->getMockBuilder(PhpConditionDataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $node->expects($this->once())
            ->method('getPhpResult')
            ->with($phpConditionDataObject);

        $tree = new ConditionTree($node, new Result);
        $tree->getCssConditions();
        $tree->getJavaScriptConditions();
        $tree->getPhpResult($phpConditionDataObject);
    }
}
