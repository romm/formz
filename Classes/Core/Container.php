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

namespace Romm\Formz\Core;

use Romm\Formz\Service\Traits\SelfInstantiateTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Container\Container as ExtbaseContainer;

/**
 * This container allows injecting dependencies in objects. It is used on
 * objects that were fetched from cache.
 *
 * Unfortunately, Extbase container does not have a public method for this, so
 * this is a pure copy of the original container functions.
 */
class Container extends ExtbaseContainer
{
    use SelfInstantiateTrait;

    /**
     * @var ExtbaseContainer
     */
    protected $extbaseContainer;

    /**
     * Injects extbase real container.
     */
    public function __construct()
    {
        $this->extbaseContainer = GeneralUtility::makeInstance(ExtbaseContainer::class);
    }

    /**
     * @param object $instance
     */
    public function injectDependenciesInInstance($instance)
    {
        $classSchema = $this->getReflectionService()->getClassSchema(get_class($instance));
        $this->extbaseContainer->injectDependencies($instance, $classSchema);
    }
}
