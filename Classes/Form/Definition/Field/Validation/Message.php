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

namespace Romm\Formz\Form\Definition\Field\Validation;

use Romm\ConfigurationObject\Traits\ConfigurationObject\ArrayConversionTrait;
use Romm\Formz\Form\Definition\AbstractFormDefinitionComponent;

class Message extends AbstractFormDefinitionComponent
{
    use ArrayConversionTrait;

    /**
     * @var string
     * @validate NotEmpty
     */
    private $identifier;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $extension;

    /**
     * @var string
     */
    protected $value;

    /**
     * @param string $identifier
     */
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->checkDefinitionFreezeState();

        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param string $extension
     */
    public function setExtension($extension)
    {
        $this->checkDefinitionFreezeState();

        $this->extension = $extension;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->checkDefinitionFreezeState();

        $this->value = $value;
    }
}
