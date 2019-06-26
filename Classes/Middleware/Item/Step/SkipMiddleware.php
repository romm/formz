<?php
declare(strict_types=1);

namespace Romm\Formz\Middleware\Item\Step;

use Romm\Formz\Middleware\Argument\Arguments;
use Romm\Formz\Middleware\Item\AbstractMiddleware;
use Romm\Formz\Middleware\Item\Begin\BeginSignal;
use Romm\Formz\Middleware\Signal\Before;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SkipMiddleware extends AbstractMiddleware implements Before, BeginSignal
{
    /**
     * @param Arguments $arguments
     */
    public function before(Arguments $arguments)
    {
        $request = $this->getRequest();

        if (!$request->hasArgument('skip')
            || !$request->getArgument('skip')
        ) {
            return;
        }

        $formName = $this->getFormObject()->getName();

        if (!$request->hasArgument($formName)) {
            return;
        }

        if (!$request->hasArgument('step')) {
            return;
        }

        $step = $request->getArgument('step');
        $definition = $this->getFormObject()->getDefinition();

        if (!$definition->hasSteps()
            || !$definition->getSteps()->hasEntry($step)
        ) {
            return;
        }

        $currentStep = $definition->getSteps()->getEntry($step);

        /** @var array $formArray */
        $formArray = $request->getArgument($formName);

        $skipSubsteps = [];

        if ($request->hasArgument('skipSubsteps')) {
            $skipSubsteps = GeneralUtility::trimExplode(',', $request->getArgument('skipSubsteps'));
        }

        if (empty($skipSubsteps)) {
            foreach ($currentStep->getSupportedFields() as $field) {
                unset($formArray[$field->getField()->getName()]);
            }
        } elseif ($currentStep->hasSubsteps()) {
            $substeps = $currentStep->getSubsteps();

            foreach ($skipSubsteps as $substep) {
                if (!$substeps->hasEntry($substep)) {
                    continue;
                }

                foreach ($substeps->getEntry($substep)->getSupportedFields() as $field) {
                    unset($formArray[$field->getField()->getName()]);
                }
            }
        }

        $request->setArgument($formName, $formArray);
    }
}
