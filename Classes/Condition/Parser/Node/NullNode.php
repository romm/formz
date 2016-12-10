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

namespace Romm\Formz\Condition\Parser\Node;

use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class NullNode extends AbstractNode implements SingletonInterface
{
    /**
     * @var NullNode
     */
    private static $instance;

    /**
     * @return NullNode
     */
    public static function get()
    {
        if (null === self::$instance) {
            self::$instance = GeneralUtility::makeInstance(self::class);
        }

        return self::$instance;
    }

    /**
     * @inheritdoc
     */
    public function getCssResult()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getJavaScriptResult()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getPhpResult(PhpConditionDataObject $dataObject)
    {
        return true;
    }
}
