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

namespace Romm\Formz\Middleware\Item\Persistence;

use Romm\Formz\Domain\Model\FormMetadata;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Middleware\Argument\Arguments;

class PersistenceFetchingArguments extends Arguments
{
    /**
     * @param string        $hash
     * @param FormMetadata  $metadata
     * @param FormInterface $form
     */
    public function __construct($hash, $metadata, $form)
    {
        $this->add('hash', $hash);
        $this->add('metadata', $metadata);
        $this->add('form', $form);
    }

    /**
     * @return bool
     */
    public function hashIsValid()
    {
        return null !== $this->getHash()
            && null !== $this->getMetadata();
    }

    /**
     * @return bool
     */
    public function formWasFound()
    {
        return null !== $this->getForm();
    }

    /**
     * @return string|null
     */
    public function getHash()
    {
        return $this->get('hash')->getValue();
    }

    /**
     * @return FormMetadata|null
     */
    public function getMetadata()
    {
        return $this->get('metadata')->getValue();
    }

    /**
     * @return FormInterface|null
     */
    public function getForm()
    {
        return $this->get('form')->getValue();
    }
}
