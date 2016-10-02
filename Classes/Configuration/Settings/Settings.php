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

namespace Romm\Formz\Configuration\Settings;

use Romm\Formz\Configuration\AbstractFormzConfiguration;
use TYPO3\CMS\Core\Cache\Backend\FileBackend;

class Settings extends AbstractFormzConfiguration
{

    /**
     * @var string
     */
    protected $defaultBackendCache = FileBackend::class;

    /**
     * @var \Romm\Formz\Configuration\Form\Settings\FormSettings
     */
    protected $defaultFormSettings;

    /**
     * @var \Romm\Formz\Configuration\Form\Field\Settings\FieldSettings
     */
    protected $defaultFieldSettings;

    /**
     * @return string
     */
    public function getDefaultBackendCache()
    {
        return $this->defaultBackendCache;
    }

    /**
     * @return \Romm\Formz\Configuration\Form\Settings\FormSettings
     */
    public function getDefaultFormSettings()
    {
        return $this->defaultFormSettings;
    }

    /**
     * @return \Romm\Formz\Configuration\Form\Field\Settings\FieldSettings
     */
    public function getDefaultFieldSettings()
    {
        return $this->defaultFieldSettings;
    }
}
