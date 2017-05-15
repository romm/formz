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

namespace Romm\Formz\Configuration\View\Classes;

use Romm\Formz\Configuration\AbstractConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Classes extends AbstractConfiguration
{
    /**
     * @var \Romm\Formz\Configuration\View\Classes\ViewClass
     */
    protected $errors;

    /**
     * @var \Romm\Formz\Configuration\View\Classes\ViewClass
     */
    protected $valid;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->errors = GeneralUtility::makeInstance(ViewClass::class);
        $this->errors->attachParent($this);

        $this->valid = GeneralUtility::makeInstance(ViewClass::class);
        $this->valid->attachParent($this);
    }

    /**
     * @return ViewClass
     */
    public function getErrorsClasses()
    {
        return $this->errors;
    }

    /**
     * @return ViewClass
     */
    public function getValidClasses()
    {
        return $this->valid;
    }
}
