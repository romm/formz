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

namespace Romm\Formz\Form\FormObject;

use Romm\Formz\Core\Core;
use Romm\Formz\Form\Definition\FormDefinition;
use Romm\Formz\Form\FormObject\Definition\FormDefinitionObject;
use Romm\Formz\Form\FormObject\Service\FormObjectConfiguration;
use Romm\Formz\Service\HashService;
use TYPO3\CMS\Extbase\Error\Result;

class FormObjectStatic
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @var FormDefinitionObject
     */
    protected $definition;

    /**
     * @var string
     */
    protected $objectHash;

    /**
     * @var FormObjectConfiguration
     */
    protected $configurationService;

    /**
     * @param string               $className
     * @param FormDefinitionObject $definition
     */
    public function __construct($className, FormDefinitionObject $definition)
    {
        $this->className = $className;
        $this->definition = $definition;
        $this->configurationService = Core::instantiate(FormObjectConfiguration::class, $this, $definition);
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return FormDefinition
     */
    public function getDefinition()
    {
        return $this->definition->getDefinition();
    }

    /**
     * This function will merge and return the validation results of both the
     * global FormZ configuration object, and this form configuration object.
     *
     * @return Result
     */
    public function getDefinitionValidationResult()
    {
        return $this->configurationService->getConfigurationValidationResult();
    }

    /**
     * Returns the hash of the form object, which should be calculated only once
     * for performance concerns.
     *
     * @return string
     */
    public function getObjectHash()
    {
        if (null === $this->objectHash) {
            $this->objectHash = $this->calculateObjectHash();
        }

        return $this->objectHash;
    }

    /**
     * Returns the calculated hash of the form object.
     *
     * @return string
     */
    protected function calculateObjectHash()
    {
        /*
         * Triggering the validation result calculation, to be sure the values
         * will be in the serialization string.
         */
        $this->getDefinitionValidationResult();

        return HashService::get()->getHash(serialize($this));
    }
}
