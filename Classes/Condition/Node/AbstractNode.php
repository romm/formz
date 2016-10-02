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
use Romm\Formz\Condition\Processor\CssProcessor;
use Romm\Formz\Condition\Processor\JavaScriptProcessor;
use Romm\Formz\Condition\Processor\PhpProcessor;
use Romm\Formz\Configuration\Form\Field\Field;

abstract class AbstractNode implements NodeInterface
{

    /**
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * Binds a node factory to this node.
     *
     * @param NodeFactory $nodeFactory
     * @return $this
     */
    public function setNodeFactory(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;

        return $this;
    }

    /**
     * @return AbstractProcessor
     */
    protected function getProcessor()
    {
        return $this->nodeFactory->getProcessor();
    }

    /**
     * Will return the result for a given field name, depending on the processor
     * bound to this node.
     *
     * @param Field $field
     * @return mixed|null
     * @throws \Exception
     */
    final public function getResult(Field $field)
    {
        switch (get_class($this->getProcessor())) {
            case CssProcessor::class:
                $result = $this->getCssResult($field);
                break;
            case JavaScriptProcessor::class:
                $result = $this->getJavaScriptResult($field);
                break;
            case PhpProcessor::class:
                $result = $this->getPhpResult($field);
                break;
            default:
                throw new \Exception('To get the result of a node, you need to bind a processor.', 1458035467);
        }

        return $result;
    }
}
