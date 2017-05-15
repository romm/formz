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

namespace Romm\Formz\Configuration\Settings;

use Romm\Formz\Configuration\AbstractConfiguration;
use Romm\Formz\Form\Definition\Field\Settings\FieldSettings;
use Romm\Formz\Form\Definition\Settings\FormSettings;
use TYPO3\CMS\Core\Cache\Backend\FileBackend;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Settings extends AbstractConfiguration
{
    /**
     * @var \Romm\Formz\Form\Definition\Settings\FormSettings
     */
    protected $defaultFormSettings;

    /**
     * @var \Romm\Formz\Form\Definition\Field\Settings\FieldSettings
     */
    protected $defaultFieldSettings;

    /**
     * @var string
     * @validate Romm.ConfigurationObject:ClassImplements(interface=TYPO3\CMS\Core\Cache\Backend\BackendInterface)
     */
    protected $defaultBackendCache = FileBackend::class;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->defaultFormSettings = GeneralUtility::makeInstance(FormSettings::class);
        $this->defaultFieldSettings = GeneralUtility::makeInstance(FieldSettings::class);
    }

    /**
     * @return FormSettings
     */
    public function getDefaultFormSettings()
    {
        return $this->defaultFormSettings;
    }

    /**
     * @return FieldSettings
     */
    public function getDefaultFieldSettings()
    {
        return $this->defaultFieldSettings;
    }

    /**
     * @return string
     */
    public function getDefaultBackendCache()
    {
        return $this->defaultBackendCache;
    }

    /**
     * @param string $defaultBackendCache
     */
    public function setDefaultBackendCache($defaultBackendCache)
    {
        $this->checkConfigurationFreezeState();

        $this->defaultBackendCache = $defaultBackendCache;
    }
}
