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

namespace Romm\Formz\Persistence\Item\Repository;

use Romm\Formz\Persistence\Option\AbstractOptionDefinition;

class RepositoryPersistenceOption extends AbstractOptionDefinition
{
    /**
     * @var string
     * @validate NotEmpty
     * @validate Romm.ConfigurationObject:ClassImplements(interface=TYPO3\CMS\Extbase\Persistence\RepositoryInterface)
     */
    protected $repositoryClassName;

    /**
     * @return string
     */
    public function getRepositoryClassName()
    {
        return $this->repositoryClassName;
    }
}
