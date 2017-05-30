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

namespace Romm\Formz\Domain\Repository;

use Romm\Formz\Domain\Model\FormMetadata;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @method FormMetadata findOneByHash(string $hash)
 */
class FormMetadataRepository extends Repository
{
    /**
     * @param string $className
     * @param string $identifier
     * @return FormMetadata
     */
    public function findOneByClassNameAndIdentifier($className, $identifier)
    {
        $query = $this->createQuery();

        /** @var FormMetadata $result */
        $result = $query
            ->matching(
                $query->logicalAnd(
                    $query->equals('className', $className),
                    $query->equals('identifier', $identifier)
                )
            )
            ->setLimit(1)
            ->execute()
            ->getFirst();

        return $result;
    }
}
