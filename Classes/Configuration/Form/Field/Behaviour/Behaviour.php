<?php
/*
 * 2017 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Formz project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\Configuration\Form\Field\Behaviour;

use Romm\Formz\Configuration\AbstractFormzConfiguration;

class Behaviour extends AbstractFormzConfiguration
{

    /**
     * @var string
     * @validate NotEmpty
     * @validate Romm.Formz:Internal\ClassExists
     */
    protected $className;

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }
}
