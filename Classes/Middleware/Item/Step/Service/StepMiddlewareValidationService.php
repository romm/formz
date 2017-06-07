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

namespace Romm\Formz\Middleware\Item\Step\Service;

use Romm\Formz\Core\Core;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Form\Definition\Step\Step\Step;
use Romm\Formz\Form\Definition\Step\Step\StepDefinition;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Form\FormObject\Service\Step\FormStepPersistence;
use Romm\Formz\Middleware\Item\FormValidation\FormValidationMiddlewareOption;
use Romm\Formz\Validation\Validator\Form\AbstractFormValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;

class StepMiddlewareValidationService
{
    /**
     * @var FormObject
     */
    protected $formObject;

    /**
     * @var StepMiddlewareService
     */
    protected $service;

    /**
     * @var FormStepPersistence
     */
    protected $persistence;

    /**
     * @param StepMiddlewareService $service
     */
    public function __construct(StepMiddlewareService $service)
    {
        $this->service = $service;
        $this->formObject = $service->getFormObject();
        $this->persistence = $service->getStepPersistence();
    }

    /**
     * Marks the given step as validated: no errors were found during validation
     * with the given values array.
     *
     * @param StepDefinition $stepDefinition
     * @param array          $formValues
     */
    public function markStepAsValidated(StepDefinition $stepDefinition, array $formValues)
    {
        $this->persistence->markStepAsValidated($stepDefinition);

        if ($this->persistence->hasStepFormValues($stepDefinition)
            && serialize($formValues) !== serialize($this->persistence->getStepFormValues($stepDefinition))
        ) {
            $this->persistence->resetValidationData();
        }

        $this->persistence->addStepFormValues($stepDefinition, $formValues);
    }

    /**
     * @param array $validatedFields
     */
    public function addValidatedFields(array $validatedFields)
    {
        $this->persistence->addValidatedFields($validatedFields);
    }

    /**
     * Checks that the previous step has already been validated, meaning the
     * user has the right to stand in the given step.
     *
     * @param StepDefinition $stepDefinition
     * @return bool
     */
    public function stepDefinitionIsValid(StepDefinition $stepDefinition)
    {
        if (false === $stepDefinition->hasPreviousDefinition()) {
            /*
             * No previous step definition found: the user stands on the first
             * step, it always has the right to stand there.
             */
            return true;
        }

        $previousStep = $stepDefinition->getPreviousDefinition()->getStep();
        $stepLevel = $stepDefinition->getStepLevel();

        return $this->persistence->stepWasValidated($previousStep)
            && true === $this->persistence->hasStepIdentifierAtLevel($stepLevel)
            && $stepDefinition->getStep()->getIdentifier() === $this->persistence->getStepIdentifierAtLevel($stepLevel);
    }

    /**
     * Searches for the first invalid step among previous steps from the given
     * step.
     *
     * All previous steps are listed, then for each one we check if submitted
     * form values has been saved in the step persistence, in which case the
     * step validation is launched again with the current form configuration.
     *
     * @param Step $step
     * @return StepDefinition|null
     */
    public function getFirstInvalidStep(Step $step)
    {
        $firstStep = $this->service->getFirstStepDefinition();

        if ($step === $firstStep->getStep()) {
            /*
             * The first step is always valid.
             */
            return null;
        }

        /*
         * If there is no form instance, and the request is not in the first
         * step, obviously the user should not be there.
         */
        if (false === $this->formObject->hasForm()) {
            return $firstStep;
        }

        /** @var StepDefinition[] $stepDefinitionsToTest */
        $stepDefinitionsToTest = [];
        $invalidStepDefinition = null;
        $currentStepDefinition = $stepDefinition = $this->service->getStepDefinition($step);

        while ($stepDefinition->hasPreviousDefinition()) {
            $stepDefinition = $stepDefinition->getPreviousDefinition();

            if ($stepDefinition->hasActivation()) {
                if (true === $this->service->getStepDefinitionConditionResult($stepDefinition)) {
                    array_unshift($stepDefinitionsToTest, $stepDefinition);
                }
            } else {
                array_unshift($stepDefinitionsToTest, $stepDefinition);
            }
        }

        foreach ($stepDefinitionsToTest as $stepDefinition) {
            $step = $stepDefinition->getStep();

            /*
             * If the already submitted form values are not found, the step is
             * considered as invalid.
             */
            if (false === $this->persistence->hasStepFormValues($stepDefinition)) {
                $invalidStepDefinition = $stepDefinition;
                break;
            }

            $result = $this->validateStep($step);

            if ($result->hasErrors()) {
                $invalidStepDefinition = $stepDefinition;
                break;
            } else {
                $this->persistence->markStepAsValidated($stepDefinition);
                $this->persistence->addValidatedFields($result->getValidatedFields());
            }
        }

        $nextStepDefinition = $this->service->getNextStepDefinition($stepDefinition);

        if ($nextStepDefinition !== $currentStepDefinition) {
            $invalidStepDefinition = $stepDefinition;
        }

        return $invalidStepDefinition;
    }

    /**
     * @param array $stepFormValues
     * @return PropertyMappingConfiguration
     */
    protected function getPropertyMappingConfiguration(array $stepFormValues)
    {
        /** @var PropertyMappingConfiguration $propertyMappingConfiguration */
        $propertyMappingConfiguration = GeneralUtility::makeInstance(PropertyMappingConfiguration::class);
        $propertyMappingConfiguration->allowAllProperties();
        $propertyMappingConfiguration->setTypeConverterOption(PersistentObjectConverter::class, PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, true);

        foreach ($stepFormValues as $key => $value) {
            if (is_array($value)) {
                $propertyMappingConfiguration->forProperty($key)->allowAllProperties();
            }
        }

        return $propertyMappingConfiguration;
    }

    /**
     * Validates (again) the given step with the form data that were previously
     * submitted and fetched from the step persistence.
     *
     * @param Step $step
     * @return FormResult
     */
    protected function validateStep(Step $step)
    {
        /** @var PropertyMapper $propertyMapper */
        $propertyMapper = Core::instantiate(PropertyMapper::class);

        $stepFormValues = $this->persistence->getMergedFormValues();
        $propertyMappingConfiguration = $this->getPropertyMappingConfiguration($stepFormValues);

        $form = $propertyMapper->convert($stepFormValues, $this->formObject->getClassName(), $propertyMappingConfiguration);

        /** @var FormValidationMiddlewareOption $formValidationMiddlewareOptions */
        $formValidationMiddlewareOptions = $this->formObject
            ->getDefinition()
            ->getPresetMiddlewares()
            ->getFormValidationMiddleware()
            ->getOptions();

        /** @var AbstractFormValidator $validator */
        $validator = Core::instantiate(
            $formValidationMiddlewareOptions->getFormValidatorClassName(),
            [
                'name'  => $this->formObject->getName(),
                'dummy' => true
            ]
        );

        $validator->getDataObject()->setValidatedStep($step);

        return $validator->validate($form);
    }
}
