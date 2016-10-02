<?php
/*
 * 2016 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Formz project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\Condition\Node;

use Romm\Formz\Condition\Processor\AbstractProcessor;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Node factory used to easily get correct instances of nodes.
 */
class NodeFactory
{

    /**
     * @var AbstractProcessor
     */
    protected $processor;

    /**
     * Constructor.
     *
     * @param AbstractProcessor $processor The current processor, which will be used by the nodes created in this factory.
     */
    public function __construct(AbstractProcessor $processor = null)
    {
        $this->processor = $processor;
    }

    /**
     * Will correctly create and perform the checks to get an instance of a
     * node.
     *
     * @param string $nodeClassName The type of the node.
     * @param array  $arguments     Arguments sent to the node constructor.
     * @return AbstractNode
     * @throws \Exception
     */
    public function getNode($nodeClassName, array $arguments = [])
    {
        if (false === class_exists($nodeClassName)) {
            throw new \Exception('The node class name "' . $nodeClassName . '" does not exist.', 1457622100);
        }

        $arguments = array_merge([$nodeClassName], $arguments);
        /** @var AbstractNode $node */
        $node = call_user_func_array(
            [GeneralUtility::class, 'makeInstance'],
            $arguments
        );

        if (false === is_object($node)
            || false === $node instanceof AbstractNode
        ) {
            throw new \Exception('The class name "' . $nodeClassName . '" is not an instance of "' . AbstractNode::class . '".', 1457623520);
        }

        $node->setNodeFactory($this);

        return $node;
    }

    /**
     * @return AbstractProcessor
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * @param AbstractProcessor $processor
     */
    public function setProcessor($processor)
    {
        $this->processor = $processor;
    }

    /**
     * When this instance is saved in TYPO3 cache, we need not to store all the
     * properties to increase performance.
     *
     * @return array
     */
    public function __sleep()
    {
        return [];
    }

}
