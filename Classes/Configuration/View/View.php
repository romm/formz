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

use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorInterface;
use Romm\Formz\Configuration\AbstractConfiguration;
use Romm\Formz\Configuration\View\Classes\Classes;
use Romm\Formz\Configuration\View\Layouts\LayoutGroup;
use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Exceptions\EntryNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class View extends AbstractConfiguration implements DataPreProcessorInterface
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
     * @param string $name
     * @return LayoutGroup
     * @throws DuplicateEntryException
     */
    public function addLayout($name)
    {
        $this->checkConfigurationFreezeState();

        if ($this->hasLayout($name)) {
            throw DuplicateEntryException::viewLayoutAlreadyAdded($name);
        }

        /** @var LayoutGroup $layout */
        $layout = GeneralUtility::makeInstance(LayoutGroup::class, $name);
        $layout->attachParent($this);

        $this->layouts[$name] = $layout;

        return $layout;
    }

    /**
     * @param string $key
     * @param string $path
     */
    public function setLayoutRootPath($key, $path)
    {
        $this->checkConfigurationFreezeState();

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
        $this->checkConfigurationFreezeState();

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

    /**
     * @param DataPreProcessor $processor
     */
    public static function dataPreProcessor(DataPreProcessor $processor)
    {
        $data = $processor->getData();

        /*
         * Forcing the names of the layouts: they are the keys of the array
         * entries.
         */
        foreach ($data['layouts'] as $key => $field) {
            $data['layouts'][$key]['name'] = $key;
        }

        $processor->setData($data);
    }
}
