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

namespace Romm\Formz\Form\FormObject\Builder;

use Romm\Formz\Form\FormObject\FormObjectStatic;
use TYPO3\CMS\Core\SingletonInterface;

interface FormObjectBuilderInterface extends SingletonInterface
{
    /**
     * @param string $className
     * @return FormObjectStatic
     */
    public function getStaticInstance($className);
}
