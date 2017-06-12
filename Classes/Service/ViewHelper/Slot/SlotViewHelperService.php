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

namespace Romm\Formz\Service\ViewHelper\Slot;

use Closure;
use Romm\Formz\Exceptions\EntryNotFoundException;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

class SlotViewHelperService implements SingletonInterface
{
    /**
     * @var SlotContextEntry[]
     */
    protected $contextEntries = [];

    /**
     * @param RenderingContextInterface $renderingContext
     */
    public function activate(RenderingContextInterface $renderingContext)
    {
        $this->contextEntries[] = GeneralUtility::makeInstance(SlotContextEntry::class, $renderingContext);
    }

    /**
     * Removes the current context entry.
     */
    public function resetState()
    {
        array_pop($this->contextEntries);
    }

    /**
     * @see \Romm\Formz\Service\ViewHelper\Slot\SlotContextEntry::addSlot()
     *
     * @param string  $name
     * @param Closure $closure
     * @param array   $arguments
     */
    public function addSlot($name, Closure $closure, array $arguments)
    {
        $this->getCurrentContext()->addSlot($name, $closure, $arguments);
    }

    /**
     * @see \Romm\Formz\Service\ViewHelper\Slot\SlotContextEntry::getSlotClosure()
     *
     * @param string $name
     * @return Closure
     * @throws EntryNotFoundException
     */
    public function getSlotClosure($name)
    {
        return $this->getCurrentContext()->getSlotClosure($name);
    }

    /**
     * @see \Romm\Formz\Service\ViewHelper\Slot\SlotContextEntry::getSlotArguments()
     *
     * @param string $name
     * @return array
     * @throws EntryNotFoundException
     */
    public function getSlotArguments($name)
    {
        return $this->getCurrentContext()->getSlotArguments($name);
    }

    /**
     * @see \Romm\Formz\Service\ViewHelper\Slot\SlotContextEntry::hasSlot()
     *
     * @param string $name
     * @return bool
     */
    public function hasSlot($name)
    {
        return $this->getCurrentContext()->hasSlot($name);
    }

    /**
     * @see \Romm\Formz\Service\ViewHelper\Slot\SlotContextEntry::addTemplateVariables()
     *
     * @param string $slotName
     * @param array  $arguments
     */
    public function addTemplateVariables($slotName, array $arguments)
    {
        $this->getCurrentContext()->addTemplateVariables($slotName, $arguments);
    }

    /**
     * @see \Romm\Formz\Service\ViewHelper\Slot\SlotContextEntry::restoreTemplateVariables()
     *
     * @param string $slotName
     */
    public function restoreTemplateVariables($slotName)
    {
        $this->getCurrentContext()->restoreTemplateVariables($slotName);
    }

    /**
     * @return SlotContextEntry
     */
    protected function getCurrentContext()
    {
        return end($this->contextEntries);
    }
}
