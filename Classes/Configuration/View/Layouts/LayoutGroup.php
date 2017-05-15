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

namespace Romm\Formz\Configuration\View\Layouts;

use Romm\Formz\Configuration\AbstractConfiguration;
use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Exceptions\EntryNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LayoutGroup extends AbstractConfiguration
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var \Romm\Formz\Configuration\View\Layouts\Layout[]
     */
    protected $items = [];

    /**
     * @var string
     * @validate NotEmpty
     */
    protected $templateFile;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Layout[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasItem($name)
    {
        return true === isset($this->items[$name]);
    }

    /**
     * @param string $name
     * @return Layout
     * @throws EntryNotFoundException
     */
    public function getItem($name)
    {
        if (false === $this->hasItem($name)) {
            throw EntryNotFoundException::viewLayoutItemNotFound($name);
        }

        return $this->items[$name];
    }

    /**
     * @param string $name
     * @return Layout
     * @throws DuplicateEntryException
     */
    public function addItem($name)
    {
        $this->checkConfigurationFreezeState();

        if ($this->hasItem($name)) {
            throw DuplicateEntryException::layoutItemAlreadyAdded($name, $this);
        }

        /** @var Layout $layout */
        $layout = GeneralUtility::makeInstance(Layout::class);
        $layout->attachParent($this);

        $this->items[$name] = $layout;

        return $layout;
    }

    /**
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->templateFile;
    }

    /**
     * @param string $templateFile
     */
    public function setTemplateFile($templateFile)
    {
        $this->checkConfigurationFreezeState();

        $this->templateFile = $templateFile;
    }
}
