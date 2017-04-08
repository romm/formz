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

namespace Romm\Formz\Form;

use Romm\Formz\Service\HashService;

class FormObjectHash
{
    /**
     * @var FormObject
     */
    protected $formObject;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @param FormObject $formObject
     */
    public function __construct(FormObject $formObject)
    {
        $this->formObject = $formObject;
    }

    /**
     * Returns the hash of the form object, which should be calculated only once
     * for performance concerns.
     *
     * @return string
     */
    public function getHash()
    {
        if (null === $this->hash) {
            $this->hash = $this->calculateHash();
        }

        return $this->hash;
    }

    /**
     * Returns the calculated hash of the form object.
     *
     * @return string
     */
    protected function calculateHash()
    {
        return HashService::get()->getHash(serialize($this->formObject));
    }

    /**
     * Resets the hash, which will be calculated on next access.
     */
    public function resetHash()
    {
        $this->hash = null;
    }
}
