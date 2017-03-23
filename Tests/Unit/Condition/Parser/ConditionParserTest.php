<?php
namespace Romm\Formz\Tests\Unit\Condition\Parser;

use Romm\Formz\Condition\Items\FieldHasValueCondition;
use Romm\Formz\Condition\Items\FieldIsValidCondition;
use Romm\Formz\Condition\Parser\ConditionParser;
use Romm\Formz\Condition\Parser\ConditionTree;
use Romm\Formz\Condition\Parser\Node\BooleanNode;
use Romm\Formz\Condition\Parser\Node\ConditionNode;
use Romm\Formz\Condition\Parser\Node\NodeInterface;
use Romm\Formz\Condition\Parser\Node\NullNode;
use Romm\Formz\Configuration\Form\Field\Activation\Activation;
use Romm\Formz\Configuration\Form\Field\Activation\EmptyActivation;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class ConditionParserTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function parserReturnsTree()
    {
        /** @var ConditionParser $parser */
        $parser = $this->getMockBuilder(ConditionParser::class)
            ->setMethods(['getNodeRecursive'])
            ->getMock();

        $tree = $parser->parse(new Activation);

        $this->assertInstanceOf(ConditionTree::class, $tree);
    }

    /**
     * If an instance of `EmptyActivation` is given to the parser, a tree must
     * be returned.
     *
     * @test
     */
    public function parsingEmptyActivationReturnsTree()
    {
        $parser = new ConditionParser;
        $tree = $parser->parse(new EmptyActivation);

        $this->assertInstanceOf(ConditionTree::class, $tree);
    }

    /**
     * Checks that a new instance of tree is returned at each call.
     *
     * @test
     */
    public function parserReturnsNewInstanceOfTree()
    {
        $parser = new ConditionParser;
        $tree1 = $parser->parse(new EmptyActivation);
        $tree2 = $parser->parse(new EmptyActivation);

        $this->assertNotSame($tree1, $tree2);
    }

    /**
     * Will parse a set of conditions and check that the tree expression
     * returned by the function `getTreeExpression()` matches the one that was
     * previously saved.
     *
     * The result must not contain any error.
     *
     * @test
     * @dataProvider parseConditionReturnsTreeExpressionDataProvider
     * @param array  $conditions
     * @param string $expression
     * @param string $treeExpression
     */
    public function parseConditionReturnsTreeExpression(array $conditions, $expression, $treeExpression)
    {
        $parser = new ConditionParser;
        $activation = new Activation;

        foreach ($conditions as $name => $condition) {
            $activation->addCondition($name, $condition);
        }

        $activation->setExpression($expression);

        $tree = $parser->parse($activation);

        $this->assertFalse($tree->getValidationResult()->hasErrors());
        $this->assertEquals(
            $treeExpression,
            $this->getTreeExpression($tree)
        );
    }

    /**
     * @return array
     */
    public function parseConditionReturnsTreeExpressionDataProvider()
    {
        return [
            /*
             * Empty expression.
             */
            [
                'conditions'     => [],
                'expression'     => '',
                'treeExpression' => 'nullNode'
            ],
            /*
             * Classic single expression with `FieldHasValueCondition` type.
             */
            [
                'conditions'     => [
                    'foo' => $this->getFieldHasValueCondition('foo', 'bar')
                ],
                'expression'     => 'foo',
                'treeExpression' => 'condition:fieldHasValue(foo;bar)'
            ],
            /*
             * Classic single expression with `FieldIsValidCondition` type.
             */
            [
                'conditions'     => [
                    'foo' => $this->getFieldIsValidCondition('baz')
                ],
                'expression'     => 'foo',
                'treeExpression' => 'condition:fieldIsValid(baz)'
            ],
            /*
             * Classic "and" expression.
             */
            [
                'conditions'     => [
                    'foo' => $this->getFieldHasValueCondition('foo', 'bar'),
                    'bar' => $this->getFieldIsValidCondition('baz')
                ],
                'expression'     => 'foo && bar',
                'treeExpression' => 'condition:fieldHasValue(foo;bar)<->bool(&&)<->condition:fieldIsValid(baz)'
            ],
            /*
             * Classic "or" expression.
             */
            [
                'conditions'     => [
                    'foo' => $this->getFieldHasValueCondition('foo', 'bar'),
                    'bar' => $this->getFieldIsValidCondition('baz')
                ],
                'expression'     => 'foo || bar',
                'treeExpression' => 'condition:fieldHasValue(foo;bar)<->bool(||)<->condition:fieldIsValid(baz)'
            ],
            /*
             * Classic "and" expression with parenthesis.
             */
            [
                'conditions'     => [
                    'foo' => $this->getFieldHasValueCondition('foo', 'bar'),
                    'bar' => $this->getFieldIsValidCondition('baz')
                ],
                'expression'     => '(foo && bar)',
                'treeExpression' => 'condition:fieldHasValue(foo;bar)<->bool(&&)<->condition:fieldIsValid(baz)'
            ],
            /*
             * Classic "and" expression with parenthesis, bis.
             */
            [
                'conditions'     => [
                    'foo' => $this->getFieldHasValueCondition('foo', 'bar'),
                    'bar' => $this->getFieldIsValidCondition('baz')
                ],
                'expression'     => '(foo) && (bar)',
                'treeExpression' => 'condition:fieldHasValue(foo;bar)<->bool(&&)<->condition:fieldIsValid(baz)'
            ],
            /*
             * Classic "or" expression with parenthesis.
             */
            [
                'conditions'     => [
                    'foo' => $this->getFieldHasValueCondition('foo', 'bar'),
                    'bar' => $this->getFieldIsValidCondition('baz')
                ],
                'expression'     => '(foo || bar)',
                'treeExpression' => 'condition:fieldHasValue(foo;bar)<->bool(||)<->condition:fieldIsValid(baz)'
            ],
            /*
             * Classic "or" expression with parenthesis, bis.
             */
            [
                'conditions'     => [
                    'foo' => $this->getFieldHasValueCondition('foo', 'bar'),
                    'bar' => $this->getFieldIsValidCondition('baz')
                ],
                'expression'     => '(foo) || (bar)',
                'treeExpression' => 'condition:fieldHasValue(foo;bar)<->bool(||)<->condition:fieldIsValid(baz)'
            ],
            /*
             * Triple "and" expression.
             */
            [
                'conditions'     => [
                    'foo' => $this->getFieldIsValidCondition('foo'),
                    'bar' => $this->getFieldIsValidCondition('bar'),
                    'baz' => $this->getFieldIsValidCondition('baz')
                ],
                'expression'     => 'foo && bar && baz',
                'treeExpression' => 'condition:fieldIsValid(foo)<->bool(&&)<->condition:fieldIsValid(bar)<->bool(&&)<->condition:fieldIsValid(baz)'
            ],
            /*
             * Triple "or" expression.
             */
            [
                'conditions'     => [
                    'foo' => $this->getFieldIsValidCondition('foo'),
                    'bar' => $this->getFieldIsValidCondition('bar'),
                    'baz' => $this->getFieldIsValidCondition('baz')
                ],
                'expression'     => 'foo || bar || baz',
                'treeExpression' => 'condition:fieldIsValid(foo)<->bool(||)<->condition:fieldIsValid(bar)<->bool(||)<->condition:fieldIsValid(baz)'
            ],
            /*
             * Triple "and" expression without spaces.
             */
            [
                'conditions'     => [
                    'foo' => $this->getFieldIsValidCondition('foo'),
                    'bar' => $this->getFieldIsValidCondition('bar'),
                    'baz' => $this->getFieldIsValidCondition('baz')
                ],
                'expression'     => 'foo&&bar&&baz',
                'treeExpression' => 'condition:fieldIsValid(foo)<->bool(&&)<->condition:fieldIsValid(bar)<->bool(&&)<->condition:fieldIsValid(baz)'
            ],
            /*
             * Triple "or" expression without spaces.
             */
            [
                'conditions'     => [
                    'foo' => $this->getFieldIsValidCondition('foo'),
                    'bar' => $this->getFieldIsValidCondition('bar'),
                    'baz' => $this->getFieldIsValidCondition('baz')
                ],
                'expression'     => 'foo||bar||baz',
                'treeExpression' => 'condition:fieldIsValid(foo)<->bool(||)<->condition:fieldIsValid(bar)<->bool(||)<->condition:fieldIsValid(baz)'
            ],
            /*
             * Triple "and" expression with parenthesis and without spaces.
             */
            [
                'conditions'     => [
                    'foo' => $this->getFieldIsValidCondition('foo'),
                    'bar' => $this->getFieldIsValidCondition('bar'),
                    'baz' => $this->getFieldIsValidCondition('baz')
                ],
                'expression'     => '(foo)&&(bar&&baz)',
                'treeExpression' => 'condition:fieldIsValid(foo)<->bool(&&)<->condition:fieldIsValid(bar)<->bool(&&)<->condition:fieldIsValid(baz)'
            ],
            /*
             * Triple "or" expression with parenthesis and without spaces.
             */
            [
                'conditions'     => [
                    'foo' => $this->getFieldIsValidCondition('foo'),
                    'bar' => $this->getFieldIsValidCondition('bar'),
                    'baz' => $this->getFieldIsValidCondition('baz')
                ],
                'expression'     => '(foo||bar)||(baz)',
                'treeExpression' => 'condition:fieldIsValid(foo)<->bool(||)<->condition:fieldIsValid(bar)<->bool(||)<->condition:fieldIsValid(baz)'
            ],
            /*
             * Triple "and" & "or" expression.
             */
            [
                'conditions'     => [
                    'foo' => $this->getFieldHasValueCondition('foo1', 'bar1'),
                    'bar' => $this->getFieldHasValueCondition('foo2', 'bar2'),
                    'baz' => $this->getFieldHasValueCondition('foo3', 'bar3')
                ],
                'expression'     => 'foo && bar || baz',
                'treeExpression' => 'condition:fieldHasValue(foo1;bar1)<->bool(&&)<->condition:fieldHasValue(foo2;bar2)<->bool(||)<->condition:fieldHasValue(foo3;bar3)'
            ],
            /*
             * Triple "and" & "or" expression without spaces.
             */
            [
                'conditions'     => [
                    'foo' => $this->getFieldHasValueCondition('foo1', 'bar1'),
                    'bar' => $this->getFieldHasValueCondition('foo2', 'bar2'),
                    'baz' => $this->getFieldHasValueCondition('foo3', 'bar3')
                ],
                'expression'     => 'foo&&bar||baz',
                'treeExpression' => 'condition:fieldHasValue(foo1;bar1)<->bool(&&)<->condition:fieldHasValue(foo2;bar2)<->bool(||)<->condition:fieldHasValue(foo3;bar3)'
            ],
            /*
             * Triple "and" & "or" expression with parenthesis and without spaces.
             */
            [
                'conditions'     => [
                    'foo' => $this->getFieldHasValueCondition('foo1', 'bar1'),
                    'bar' => $this->getFieldHasValueCondition('foo2', 'bar2'),
                    'baz' => $this->getFieldHasValueCondition('foo3', 'bar3')
                ],
                'expression'     => '(foo)&&(bar||baz)',
                'treeExpression' => 'condition:fieldHasValue(foo1;bar1)<->bool(&&)<->condition:fieldHasValue(foo2;bar2)<->bool(||)<->condition:fieldHasValue(foo3;bar3)'
            ],
            /*
             * Multiple stacks of parenthesis.
             */
            [
                'conditions'     => [
                    'foo' => $this->getFieldIsValidCondition('foo'),
                    'bar' => $this->getFieldIsValidCondition('bar'),
                    'baz' => $this->getFieldIsValidCondition('baz')
                ],
                'expression'     => '((foo) && (((bar))) || (baz))',
                'treeExpression' => 'condition:fieldIsValid(foo)<->bool(&&)<->condition:fieldIsValid(bar)<->bool(||)<->condition:fieldIsValid(baz)'
            ],
            /*
             * Complex condition.
             */
            [
                'conditions'     => [
                    'foo' => $this->getFieldIsValidCondition('foo'),
                    'bar' => $this->getFieldIsValidCondition('bar'),
                    'baz' => $this->getFieldIsValidCondition('baz')
                ],
                'expression'     => '(foo && bar) || (bar && baz) || (foo && baz)',
                'treeExpression' => 'condition:fieldIsValid(foo)<->bool(&&)<->condition:fieldIsValid(bar)<->bool(||)<->condition:fieldIsValid(bar)<->bool(&&)<->condition:fieldIsValid(baz)<->bool(||)<->condition:fieldIsValid(foo)<->bool(&&)<->condition:fieldIsValid(baz)'
            ]
        ];
    }

    /**
     * @todo
     *
     * @test
     * @dataProvider parseConditionReturnsErrorDataProvider
     * @param array  $conditions
     * @param string $expression
     * @param string $errorCode
     */
    public function parseConditionReturnsError(array $conditions, $expression, $errorCode)
    {
        $parser = new ConditionParser;
        $activation = new Activation;

        foreach ($conditions as $name => $condition) {
            $activation->addCondition($name, $condition);
        }

        $activation->setExpression($expression);

        $tree = $parser->parse($activation);

        $this->assertTrue($tree->getValidationResult()->hasErrors());
        $this->assertEquals($errorCode, $tree->getValidationResult()->getFirstError()->getCode());
    }

    /**
     * @return array
     */
    public function parseConditionReturnsErrorDataProvider()
    {
        return [
            [
                /*
                 * Condition that doesn't exist.
                 */
                'conditions'     => [
                    'foo' => $this->getFieldHasValueCondition('foo', 'bar')
                ],
                'expression'     => 'baz',
                'errorCode'     => ConditionParser::ERROR_CODE_CONDITION_NOT_FOUND
            ],
            /*
             * Closing parenthesis is invalid.
             */
            [
                'conditions'     => [
                    'foo' => $this->getFieldHasValueCondition('foo', 'bar')
                ],
                'expression'     => 'foo)',
                'errorCode'     => ConditionParser::ERROR_CODE_INVALID_CLOSING_PARENTHESIS
            ],
            /*
             * Closing parenthesis not found.
             */
            [
                'conditions'     => [
                    'foo' => $this->getFieldHasValueCondition('foo', 'bar')
                ],
                'expression'     => '(foo',
                'errorCode'     => ConditionParser::ERROR_CODE_CLOSING_PARENTHESIS_NOT_FOUND
            ],
            /*
             * Logical operator must be preceded by an operation.
             */
            [
                'conditions'     => [
                    'foo' => $this->getFieldHasValueCondition('foo', 'bar')
                ],
                'expression'     => '&& foo',
                'errorCode'     => ConditionParser::ERROR_CODE_LOGICAL_OPERATOR_PRECEDED
            ],
            /*
             * Logical operator must be preceded by an operation.
             */
            [
                'conditions'     => [
                    'foo' => $this->getFieldHasValueCondition('foo', 'bar')
                ],
                'expression'     => 'foo &&',
                'errorCode'     => ConditionParser::ERROR_CODE_LOGICAL_OPERATOR_FOLLOWED
            ]
        ];
    }

    /**
     * Will build and return a string based on the nodes of the given tree. This
     * is used to check if a tree was built properly.
     *
     * @param ConditionTree $tree
     * @return string
     */
    protected function getTreeExpression(ConditionTree $tree)
    {
        $expressions = [];

        $tree->alongNodes(function (NodeInterface $node) use (&$expressions) {
            if ($node instanceof BooleanNode) {
                $expressions[] = "bool({$node->getOperator()})";
            } elseif ($node instanceof ConditionNode) {
                $condition = $node->getCondition();

                if ($condition instanceof FieldHasValueCondition) {
                    $conditionExpression = $condition::CONDITION_NAME . "({$condition->getFieldName()};{$condition->getFieldValue()})";
                } elseif ($condition instanceof FieldIsValidCondition) {
                    $conditionExpression = $condition::CONDITION_NAME . "({$condition->getFieldName()})";
                } else {
                    $conditionExpression = 'UNKNOWN-CONDITION';
                }

                $expressions[] = "condition:$conditionExpression";
            } elseif ($node instanceof NullNode) {
                $expressions[] = 'nullNode';
            } else {
                $expressions[] = 'UNKNOWN-NODE';
            }
        });

        return implode('<->', $expressions);
    }

    /**
     * @param string $fieldName
     * @return FieldIsValidCondition
     */
    protected function getFieldIsValidCondition($fieldName)
    {
        $condition = new FieldIsValidCondition;
        $condition->setFieldName($fieldName);

        return $condition;
    }

    /**
     * @param string $fieldName
     * @param string $fieldValue
     * @return FieldHasValueCondition
     */
    protected function getFieldHasValueCondition($fieldName, $fieldValue)
    {
        $condition = new FieldHasValueCondition;
        $condition->setFieldName($fieldName);
        $condition->setFieldValue($fieldValue);

        return $condition;
    }
}
