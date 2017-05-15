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

namespace Romm\Formz\Condition\Parser\Tree;

use Romm\Formz\Condition\Parser\Node\NullNode;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use Romm\Formz\Service\Traits\SelfInstantiateTrait;
use TYPO3\CMS\Core\SingletonInterface;

class EmptyConditionTree extends ConditionTree implements SingletonInterface
{
    use SelfInstantiateTrait;

    public function __construct()
    {
        parent::__construct(NullNode::get());
    }

    /**
     * @return array
     */
    public function getCssConditions()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getJavaScriptConditions()
    {
        return [];
    }

    /**
     * @param PhpConditionDataObject $dataObject
     * @return bool
     */
    public function getPhpResult(PhpConditionDataObject $dataObject)
    {
        return true;
    }
}
