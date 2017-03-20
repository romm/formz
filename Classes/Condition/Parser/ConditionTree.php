<?php
/*
 * 2017 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 FormZ project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\Condition\Parser;

use Romm\Formz\Condition\Parser\Node\ActivationDependencyAwareInterface;
use Romm\Formz\Condition\Parser\Node\NodeInterface;
use Romm\Formz\Condition\Processor\ConditionProcessor;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use Romm\Formz\Configuration\Form\Field\Activation\ActivationInterface;
use TYPO3\CMS\Extbase\Error\Result;

/**
 * Tree built with instances of `NodeInterface` that represents a condition
 * instance.
 *
 * It can be used to get CSS, JavaScript or PHP results of the condition.
 */
class ConditionTree
{
    /**
     * @var NodeInterface
     */
    private $rootNode;

    /**
     * @var Result
     */
    private $validationResult;

    /**
     * @var bool
     */
    private $dependenciesWereInjected = false;

    /**
     * @param NodeInterface $rootNode
     * @param Result        $validationResult
     */
    public function __construct(NodeInterface $rootNode, Result $validationResult)
    {
        $this->rootNode = $rootNode;
        $this->validationResult = $validationResult;

        $this->rootNode->setTree($this);
    }

    /**
     * Allows to go through all the nodes and sub-nodes of the tree. The
     * callback is called for every node, with a unique argument: the node
     * instance.
     *
     * @param callable $callback
     */
    public function alongNodes(callable $callback)
    {
        $this->rootNode->along($callback);
    }

    /**
     * @param ConditionProcessor  $conditionProcessor
     * @param ActivationInterface $activation
     * @return $this
     */
    public function injectDependencies(ConditionProcessor $conditionProcessor, ActivationInterface $activation)
    {
        if (false === $this->dependenciesWereInjected) {
            $this->dependenciesWereInjected = true;

            // Looping on nodes to detect which ones have a dependency to the activation.
            $this->alongNodes(function (NodeInterface $node) use ($conditionProcessor, $activation) {
                if ($node instanceof ActivationDependencyAwareInterface) {
                    $node->injectDependencies($conditionProcessor, $activation);
                }
            });
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getCssConditions()
    {
        return $this->rootNode->getCssResult();
    }

    /**
     * @return array
     */
    public function getJavaScriptConditions()
    {
        return $this->rootNode->getJavaScriptResult();
    }

    /**
     * @param PhpConditionDataObject $dataObject
     * @return bool
     */
    public function getPhpResult(PhpConditionDataObject $dataObject)
    {
        return $this->rootNode->getPhpResult($dataObject);
    }

    /**
     * @return Result
     */
    public function getValidationResult()
    {
        return $this->validationResult;
    }

    /**
     * Resetting the dependencies injection.
     */
    public function __wakeup()
    {
        $this->dependenciesWereInjected = false;
    }
}
