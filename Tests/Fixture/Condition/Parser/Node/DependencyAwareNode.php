<?php
namespace Romm\Formz\Tests\Fixture\Condition\Parser\Node;

use Romm\Formz\Condition\Parser\Node\ActivationDependencyAwareInterface;
use Romm\Formz\Condition\Parser\Node\NullNode;
use Romm\Formz\Condition\Processor\ConditionProcessor;
use Romm\Formz\Configuration\Form\Condition\Activation\ActivationInterface;

class DependencyAwareNode extends NullNode implements ActivationDependencyAwareInterface
{
    /**
     * @param ConditionProcessor  $processor
     * @param ActivationInterface $activation
     * @return void
     */
    public function injectDependencies(ConditionProcessor $processor, ActivationInterface $activation)
    {
    }
}
