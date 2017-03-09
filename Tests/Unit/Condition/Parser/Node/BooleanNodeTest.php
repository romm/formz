<?php
namespace Romm\Formz\Tests\Unit\Condition\Parser\Node;

use Romm\Formz\Condition\Parser\ConditionParser;
use Romm\Formz\Condition\Parser\Node\BooleanNode;
use Romm\Formz\Condition\Parser\Node\NodeInterface;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use Romm\Formz\Exceptions\InvalidConfigurationException;
use Romm\Formz\Tests\Fixture\Condition\Parser\Node\DefaultNode;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class BooleanNodeTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function resultsAreValid()
    {
        /** @var PhpConditionDataObject $phpConditionDataObject */
        $phpConditionDataObject = $this->getMockBuilder(PhpConditionDataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $leftNode = new DefaultNode;
        $leftNode->setCssResult('[css-foo]');
        $leftNode->setJavaScriptResult('[js-foo]');

        $rightNode = new DefaultNode;
        $rightNode->setCssResult('[css-bar]');
        $rightNode->setJavaScriptResult('[js-bar]');

        $node = new BooleanNode($leftNode, $rightNode, ConditionParser::LOGICAL_AND);
        $this->assertEquals(['[css-foo][css-bar]'], $node->getCssResult());

        $node = new BooleanNode($leftNode, $rightNode, ConditionParser::LOGICAL_OR);
        $this->assertEquals(['[css-foo]', '[css-bar]'], $node->getCssResult());

        $node = new BooleanNode($leftNode, $rightNode, ConditionParser::LOGICAL_AND);
        $this->assertEquals(['[js-foo] && [js-bar]'], $node->getJavaScriptResult());

        $node = new BooleanNode($leftNode, $rightNode, ConditionParser::LOGICAL_OR);
        $this->assertEquals(['[js-foo]', '[js-bar]'], $node->getJavaScriptResult());

        $leftNode->setPhpResult(true);
        $rightNode->setPhpResult(false);
        $node = new BooleanNode($leftNode, $rightNode, ConditionParser::LOGICAL_AND);
        $this->assertFalse($node->getPhpResult($phpConditionDataObject));

        $leftNode->setPhpResult(false);
        $rightNode->setPhpResult(true);
        $node = new BooleanNode($leftNode, $rightNode, ConditionParser::LOGICAL_AND);
        $this->assertFalse($node->getPhpResult($phpConditionDataObject));

        $leftNode->setPhpResult(false);
        $rightNode->setPhpResult(false);
        $node = new BooleanNode($leftNode, $rightNode, ConditionParser::LOGICAL_AND);
        $this->assertFalse($node->getPhpResult($phpConditionDataObject));

        $leftNode->setPhpResult(true);
        $rightNode->setPhpResult(false);
        $node = new BooleanNode($leftNode, $rightNode, ConditionParser::LOGICAL_OR);
        $this->assertTrue($node->getPhpResult($phpConditionDataObject));

        $leftNode->setPhpResult(false);
        $rightNode->setPhpResult(true);
        $node = new BooleanNode($leftNode, $rightNode, ConditionParser::LOGICAL_OR);
        $this->assertTrue($node->getPhpResult($phpConditionDataObject));

        $leftNode->setPhpResult(true);
        $rightNode->setPhpResult(true);
        $node = new BooleanNode($leftNode, $rightNode, ConditionParser::LOGICAL_OR);
        $this->assertTrue($node->getPhpResult($phpConditionDataObject));
    }

    /**
     * @test
     */
    public function wrongOperatorThrowsException()
    {
        $this->setExpectedException(InvalidConfigurationException::class);

        $node = new BooleanNode(new DefaultNode, new DefaultNode, 'foo');
        $node->getCssResult();
    }

    /**
     * @test
     */
    public function canGoAlongNodesInRightOrder()
    {
        $leftNode = new DefaultNode;
        $rightNode = new DefaultNode;
        $i = 0;

        $booleanNode = new BooleanNode($leftNode, $rightNode, 'foo');

        $booleanNode->along(function (NodeInterface $node) use (&$i, $leftNode, $rightNode, $booleanNode) {
            switch ($i) {
                case 0:
                    $this->assertSame($leftNode, $node);
                    break;
                case 1:
                    $this->assertSame($booleanNode, $node);
                    break;
                case 2:
                    $this->assertSame($rightNode, $node);
                    break;
                default:
                    throw new \Exception("Wrong node at index #$i : " . get_class($node));
            }

            $i++;
        });
    }
}
