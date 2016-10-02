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

namespace Romm\Formz\Configuration\View;

use Romm\Formz\Configuration\AbstractFormzConfiguration;
use Romm\Formz\Configuration\View\Classes\Classes;
use Romm\Formz\Configuration\View\Layouts\LayoutGroup;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class View extends AbstractFormzConfiguration
{

    /**
     * @var \Romm\Formz\Configuration\View\Classes\Classes
     */
    protected $classes;

    /**
     * @var \ArrayObject<\Romm\Formz\Configuration\View\Layouts\LayoutGroup>
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
     * @var bool
     */
    private $layoutRootPathsWereCleaned = false;

    /**
     * @var bool
     */
    private $partialRootPathsWereCleaned = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->classes = GeneralUtility::makeInstance(Classes::class);
    }

    /**
     * @return Classes[]
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * @return LayoutGroup[]
     */
    public function getLayouts()
    {
        return $this->layouts;
    }

    /**
     * @param string $layoutName
     * @return bool
     */
    public function hasLayout($layoutName)
    {
        return (true === isset($this->layouts[$layoutName]));
    }

    /**
     * @param string $layoutName
     * @return LayoutGroup|null
     */
    public function getLayout($layoutName)
    {
        return (true === isset($this->layouts[$layoutName]))
            ? $this->layouts[$layoutName]
            : null;
    }

    /**
     * @return array
     */
    public function getLayoutRootPaths()
    {
        if (false === $this->layoutRootPathsWereCleaned) {
            $this->layoutRootPathsWereCleaned = true;
            foreach ($this->layoutRootPaths as $key => $layoutRootPath) {
                $this->layoutRootPaths[$key] = GeneralUtility::getFileAbsFileName($layoutRootPath);
            }
        }

        return $this->layoutRootPaths;
    }

    /**
     * @return array
     */
    public function getPartialRootPaths()
    {
        if (false === $this->partialRootPathsWereCleaned) {
            $this->partialRootPathsWereCleaned = true;
            foreach ($this->partialRootPaths as $key => $partialRootPath) {
                $this->partialRootPaths[$key] = GeneralUtility::getFileAbsFileName($partialRootPath);
            }
        }

        return $this->partialRootPaths;
    }
}
