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

namespace Romm\Formz\Domain\Model;

use Romm\Formz\Domain\Model\DataObject\FormMetadataObject;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Contains metadata for a form.
 *
 * It can be used to store arbitrary data that will be persisted from a request
 * to another (if the form is bound to at least one persistence service).
 *
 * The form is identified with a unique hash, which is used to retrieve the
 * metadata instance
 * .
 */
class FormMetadata extends AbstractEntity
{
    /**
     * @var string
     */
    protected $hash;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var \Romm\Formz\Domain\Model\DataObject\FormMetadataObject
     */
    protected $metadata;

    /**
     * @var bool
     */
    private $objectWasAssigned = false;

    /**
     * @param string             $hash
     * @param string             $className
     * @param string             $identifier
     * @param FormMetadataObject $metadata
     */
    public function __construct($hash, $className, $identifier, FormMetadataObject $metadata)
    {
        $this->hash = $hash;
        $this->className = $className;
        $this->identifier = $identifier;
        $this->metadata = $metadata;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return bool
     */
    public function hasIdentifier()
    {
        return null !== $this->identifier;
    }

    /**
     * @return FormMetadataObject
     */
    public function getMetadata()
    {
        if (false === $this->objectWasAssigned) {
            $this->objectWasAssigned = true;
            $this->metadata->setObject($this);
        }

        return $this->metadata;
    }
}
