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

namespace Romm\Formz\Service;

use Romm\Formz\Core\Core;
use Romm\Formz\Domain\Repository\FormMetadataRepository;
use Romm\Formz\Service\Traits\SelfInstantiateTrait;
use TYPO3\CMS\Core\SingletonInterface;

class HashService implements SingletonInterface
{
    use SelfInstantiateTrait;

    /**
     * @param string $value
     * @return string
     */
    public function getHash($value)
    {
        return hash('sha256', $value);
    }

    /**
     * @param int $length the number of bytes to generate
     * @return string the generated random bytes
     * @throws \Exception
     */
    public function getUniqueHash($length = 32)
    {
        if (!is_int($length) || $length < 1) {
            throw new \Exception('Invalid $length parameter');
        }

        /** @var FormMetadataRepository $formMetadataRepository */
        $formMetadataRepository = Core::instantiate(FormMetadataRepository::class);

        do {
            // Generated random bytes
            $hash = bin2hex(random_bytes($length));

            // Check if the hash has already been generated
            $object = $formMetadataRepository->findOneByHash($hash);

        } while ($object !== null);

        return $hash;
    }
}
