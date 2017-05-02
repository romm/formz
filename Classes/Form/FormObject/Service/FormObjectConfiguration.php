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

namespace Romm\Formz\Form\FormObject\Service;

use Romm\Formz\Configuration\ConfigurationFactory;
use Romm\Formz\Core\Core;
use Romm\Formz\Form\FormObject\Definition\FormDefinitionObject;
use Romm\Formz\Form\FormObject\FormObjectStatic;
use Romm\Formz\Validation\Validator\Internal\FormDefinitionValidator;
use TYPO3\CMS\Extbase\Error\Result;

class FormObjectConfiguration
{
    /**
     * @var FormObjectStatic
     */
    protected $static;

    /**
     * @var FormDefinitionObject
     */
    protected $definition;

    /**
     * @var Result
     */
    protected $configurationValidationResult;

    /**
     * @param FormObjectStatic     $static
     * @param FormDefinitionObject $definition
     */
    public function __construct(FormObjectStatic $static, FormDefinitionObject $definition)
    {
        $this->static = $static;
        $this->definition = $definition;
    }

    /**
     * This function will merge and return the validation results of both the
     * global FormZ configuration object, and this form configuration object.
     *
     * @return Result
     */
    public function getConfigurationValidationResult()
    {
        if (null === $this->configurationValidationResult) {
            $this->configurationValidationResult = $this->getMergedValidationResult();
        }

        return $this->configurationValidationResult;
    }

    /**
     * Resets the validation result and merges it with the global FormZ
     * configuration.
     *
     * @return Result
     */
    protected function getMergedValidationResult()
    {
        $result = new Result;
        $formPropertyName = 'forms.' . $this->static->getClassName();

        $result->merge($this->getGlobalConfigurationValidationResult());
        $result->forProperty($formPropertyName)->merge($this->definition->getValidationResult());
        $result->forProperty($formPropertyName)->merge($this->getFormDefinitionValidationResult());

        return $result;
    }

    /**
     * @return Result
     */
    protected function getFormDefinitionValidationResult()
    {
        /** @var FormDefinitionValidator $formDefinitionValidator */
        $formDefinitionValidator = Core::instantiate(FormDefinitionValidator::class);

        return $formDefinitionValidator->validate($this->definition->getDefinition());
    }

    /**
     * @return Result
     */
    protected function getGlobalConfigurationValidationResult()
    {
        return ConfigurationFactory::get()->getFormzConfiguration()->getValidationResult();
    }
}
