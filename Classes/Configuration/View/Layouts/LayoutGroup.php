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

use Romm\ConfigurationObject\Traits\ConfigurationObject\StoreArrayIndexTrait;
use Romm\Formz\Configuration\AbstractFormzConfiguration;
use Romm\Formz\Exceptions\EntryNotFoundException;

class LayoutGroup extends AbstractFormzConfiguration
{
    use StoreArrayIndexTrait;

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
     * @return string
     */
    public function getName()
    {
        return $this->getArrayIndex();
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
     * @param Layout $layout
     */
    public function setItem($name, Layout $layout)
    {
        $layout->setParents([$this]);

        $this->items[$name] = $layout;
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
        $this->templateFile = $templateFile;
    }
}
