<?php
/*
 * 2017 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Formz project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\Condition\Parser\Node;

use Romm\Formz\Condition\Processor\ConditionProcessor;
use Romm\Formz\Configuration\Form\Condition\Activation\ActivationInterface;

interface ActivationDependencyAwareInterface
{
    /**
     * @param ConditionProcessor  $processor
     * @param ActivationInterface $activation
     * @return void
     */
    public function injectDependencies(ConditionProcessor $processor, ActivationInterface $activation);
}
