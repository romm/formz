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

namespace Romm\Formz\AssetHandler\Connector;

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Contains several information regarding assets. There can be only one instance
 * of this class, so it can be used to store data for several connectors.
 */
class AssetHandlerConnectorStates implements SingletonInterface
{
    /**
     * @var bool
     */
    private $defaultAssetsIncluded = false;

    /**
     * Storage for JavaScript files which were already included. It will handle
     * multiple instance of forms in the same page, by avoiding multiple
     * inclusions of the same JavaScript files.
     *
     * @var array
     */
    private $alreadyIncludedValidationJavaScriptFiles = [];

    /**
     * @param bool $flag
     */
    public function markDefaultAssetsAsIncluded($flag = true)
    {
        $this->defaultAssetsIncluded = (bool)$flag;
    }

    /**
     * @return bool
     */
    public function defaultAssetsWereIncluded()
    {
        return $this->defaultAssetsIncluded;
    }

    /**
     * @return array
     */
    public function getAlreadyIncludedValidationJavaScriptFiles()
    {
        return $this->alreadyIncludedValidationJavaScriptFiles;
    }

    /**
     * Saves a file path as already included, so it can be retrieved later and
     * not being included twice.
     *
     * @param string $file
     */
    public function registerIncludedValidationJavaScriptFiles($file)
    {
        $this->alreadyIncludedValidationJavaScriptFiles[] = $file;
    }
}
