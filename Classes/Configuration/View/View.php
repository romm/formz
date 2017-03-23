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

namespace Romm\Formz\Configuration\View;

use Romm\Formz\Configuration\AbstractFormzConfiguration;
use Romm\Formz\Configuration\View\Classes\Classes;
use Romm\Formz\Configuration\View\Layouts\LayoutGroup;
use Romm\Formz\Exceptions\EntryNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class View extends AbstractFormzConfiguration
{

    /**
     * @var \Romm\Formz\Configuration\View\Classes\Classes
     */
    protected $classes;

    /**
     * @var \Romm\Formz\Configuration\View\Layouts\LayoutGroup[]
     */
    protected $layouts = [];

    /**
     * @var array
     * @validate NotEmpty
     */
    protected $layoutRootPaths = [];

    /**
     * @var array
     * @validate NotEmpty
     */
    protected $partialRootPaths = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->classes = GeneralUtility::makeInstance(Classes::class);
    }

    /**
     * @return Classes
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * @param string      $name
     * @param LayoutGroup $layout
     */
    public function setLayout($name, LayoutGroup $layout)
    {
        $this->layouts[$name] = $layout;
    }

    /**
     * @return LayoutGroup[]
     */
    public function getLayouts()
    {
        return $this->layouts;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasLayout($name)
    {
        return true === isset($this->layouts[$name]);
    }

    /**
     * @param string $name
     * @return LayoutGroup
     * @throws EntryNotFoundException
     */
    public function getLayout($name)
    {
        if (false === $this->hasLayout($name)) {
            throw EntryNotFoundException::viewLayoutNotFound($name);
        }

        return $this->layouts[$name];
    }

    /**
     * @param string $key
     * @param string $path
     */
    public function setLayoutRootPath($key, $path)
    {
        $this->layoutRootPaths[$key] = $path;
    }

    /**
     * @return array
     */
    public function getLayoutRootPaths()
    {
        return $this->layoutRootPaths;
    }

    /**
     * @return array
     */
    public function getAbsoluteLayoutRootPaths()
    {
        $paths = $this->layoutRootPaths;

        foreach ($paths as $key => $path) {
            $paths[$key] = $this->getAbsolutePath($path);
        }

        return $paths;
    }

    /**
     * @param string $key
     * @param string $path
     */
    public function setPartialRootPath($key, $path)
    {
        $this->partialRootPaths[$key] = $path;
    }

    /**
     * @return array
     */
    public function getPartialRootPaths()
    {
        return $this->partialRootPaths;
    }

    /**
     * @return array
     */
    public function getAbsolutePartialRootPaths()
    {
        $paths = $this->partialRootPaths;

        foreach ($paths as $key => $path) {
            $paths[$key] = $this->getAbsolutePath($path);
        }

        return $paths;
    }
}
