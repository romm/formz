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

use Romm\Formz\Form\Definition\Step\Step\Substep\Substep;
use Romm\Formz\Form\Definition\Step\Step\Substep\SubstepDefinition;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Form\FormObject\FormObjectFactory;
use Romm\Formz\Middleware\Item\Step\Service\Exception\SubstepException;
use Romm\Formz\Service\Traits\SelfInstantiateTrait;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Request;

/**
 * @todo
 */
class SubstepMiddlewareService implements SingletonInterface
{
    use SelfInstantiateTrait;

    /**
     * @var FormObject
     */
    protected $formObject;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param FormObject $formObject
     * @param Request $request
     * @return $this
     */
    public function reset(FormObject $formObject, Request $request)
    {
        $this->formObject = $formObject;
        $this->request = $request;

        return $this;
    }

    public function manageSubstepPathData()
    {
        $currentStep = $this->formObject->fetchCurrentStep($this->request)->getCurrentStep();

        if ($currentStep
            && $currentStep->hasSubsteps()
        ) {
            $this->validateAndFillSubstepPathData();
        }
    }

    protected function validateAndFillSubstepPathData()
    {
        $substepPath = $this->getSubstepPathDataFromRequest();
        $stepService = FormObjectFactory::get()->getStepService($this->formObject);

        if (null !== $substepPath) {
            try {
                $path = isset($substepPath['path']) && is_array($substepPath['path'])
                    ? $substepPath['path']
                    : [];

                $currentSubstepDefinition = $this->fetchCurrentSubstep($substepPath['current']);
                $substepPath = $this->fetchSubstepPath($path);

                $stepService->setCurrentSubstepDefinition($currentSubstepDefinition);
                $stepService->setSubstepsPath($substepPath);
            } catch (SubstepException $exception) {
            }
        }
    }

    protected function fetchCurrentSubstep($identifier)
    {
        $currentSubstep = $this->getSubstep($identifier);

        if ($currentSubstep) {
            $currentSubstepDefinition = $this->getSubstepDefinition($currentSubstep);

            if ($currentSubstepDefinition) {
                return $currentSubstepDefinition;
            }
        }

        throw new SubstepException;
    }

    /**
     * @param array $path
     * @return Substep[]
     * @throws SubstepException
     */
    protected function fetchSubstepPath(array $path)
    {
        $substepsPath = [];
        /** @var SubstepDefinition $lastSubstepDefinition */
        $lastSubstepDefinition = null;

        foreach ($path as $substepIdentifier) {
            $substepIdentifier = (string)$substepIdentifier;
            $substep = $this->getSubstep($substepIdentifier);

            if ($substep) {
                $substepDefinition = $this->getSubstepDefinition($substep);

                if ($substepDefinition) {
                    $flag = true;

                    if ($lastSubstepDefinition) {
                        $flag = ($lastSubstepDefinition->hasNextSubstep()
                            && $lastSubstepDefinition->getNextSubstep()->getSubstep()->getIdentifier() === $substepDefinition->getSubstep()->getIdentifier()
                        );

                        if (false === $flag) {
                            if ($lastSubstepDefinition->hasDivergence()) {
                                foreach ($lastSubstepDefinition->getDivergenceSubsteps() as $divergenceSubstep) {
                                    if ($divergenceSubstep->getSubstep()->getIdentifier() === $substepDefinition->getSubstep()->getIdentifier()) {
                                        $flag = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    if (true === $flag) {
                        $substepsPath[] = $substep;
                        $lastSubstepDefinition = $substepDefinition;
                        continue;
                    }
                }
            }

            throw new SubstepException;
        }

        return $substepsPath;
    }

    /**
     * @param string $identifier
     * @return Substep|null
     */
    protected function getSubstep($identifier)
    {
        $currentStep = $this->formObject->fetchCurrentStep($this->request)->getCurrentStep();

        return ($currentStep->getSubsteps()->hasEntry($identifier))
            ? $currentStep->getSubsteps()->getEntry($identifier)
            : null;
    }

    /**
     * @param Substep $substep
     * @return SubstepDefinition|null
     */
    public function getSubstepDefinition(Substep $substep)
    {
        return $this->findSubstepDefinition($substep, $this->getFirstSubstepDefinition());
    }

    /**
     * @param Substep $substep
     * @param SubstepDefinition $substepDefinition
     * @return SubstepDefinition|null
     */
    protected function findSubstepDefinition(Substep $substep, SubstepDefinition $substepDefinition)
    {
        if ($substepDefinition->getSubstep() === $substep) {
            return $substepDefinition;
        }

        if ($substepDefinition->hasNextSubstep()) {
            $result = $this->findSubstepDefinition($substep, $substepDefinition->getNextSubstep());

            if ($result instanceof SubstepDefinition) {
                return $result;
            }
        }

        if ($substepDefinition->hasDivergence()) {
            foreach ($substepDefinition->getDivergenceSubsteps() as $divergenceStep) {
                $result = $this->findSubstepDefinition($substep, $divergenceStep);

                if ($result instanceof SubstepDefinition) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * @return array|null
     */
    protected function getSubstepPathDataFromRequest()
    {
        if ($this->request->hasArgument('substepsPath')) {
            $substepPath = json_decode($this->request->getArgument('substepsPath'), true);

            if (null !== $substepPath) {
                return $substepPath;
            }
        }

        return null;
    }

    /**
     * @return SubstepDefinition
     */
    public function getFirstSubstepDefinition()
    {
        return $this->formObject->fetchCurrentStep($this->request)->getCurrentStep()->getSubsteps()->getFirstSubstepDefinition();
    }
}
