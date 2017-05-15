<?php
namespace Romm\Formz\Tests\Unit\Condition\Parser\Node;

use Romm\Formz\Condition\Parser\Node\AbstractNode;
use Romm\Formz\Condition\Parser\Node\NodeInterface;
use Romm\Formz\Condition\Parser\Node\NullNode;
use Romm\Formz\Condition\Parser\Tree\ConditionTree;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class AbstractNodeTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function variablesAreInjected()
    {
        /** @var AbstractNode $node */
        $node = $this->getMockBuilder(AbstractNode::class)
            ->getMockForAbstractClass();

        /** @var ConditionTree $tree */
        $tree = $this->getMockBuilder(ConditionTree::class)
            ->disableOriginalConstructor()
            ->getMock();

        $nullNode = new NullNode;
        $nullNode->setTree($tree);

        $node->setParent($nullNode);
        $this->assertSame($nullNode, $node->getParent());
        $this->assertSame($tree, $node->getTree());

        /** @var ConditionTree $treeBis */
        $treeBis = $this->getMockBuilder(ConditionTree::class)
            ->disableOriginalConstructor()
            ->getMock();

        $node->setTree($treeBis);
        $this->assertSame($treeBis, $node->getTree());

        $node->along(function (NodeInterface $callbackNode) use ($node) {
            $this->assertSame($node, $callbackNode);
        });
    }
}
