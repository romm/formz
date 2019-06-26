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

namespace Romm\Formz\AssetHandler\Css;

use Romm\Formz\AssetHandler\AbstractAssetHandler;
use Romm\Formz\Form\Definition\Step\Step\Substep\Substeps;

/**
 * This asset handler generates the CSS code used to display/hide a substep
 * container when the current form substep is not the correct one.
 */
class SubstepCssAssetHandler extends AbstractAssetHandler
{

    /**
     * @return string
     */
    public function getSubstepCss()
    {
        $cssBlocks = [];
        $formDefinition = $this->getFormObject()->getDefinition();

        if ($formDefinition->hasSteps()) {
            foreach ($formDefinition->getSteps()->getEntries() as $step) {
                if ($step->hasSubsteps()) {
                    $cssBlocks = array_merge($cssBlocks, $this->aze($step->getSubsteps()));
                }
            }
        }

        return implode(CRLF, $cssBlocks);
    }

    protected function aze(Substeps $substeps)
    {
        $cssBlocks = [];
        $formName = $this->getFormObject()->getName();

        foreach ($substeps->getEntries() as $substep) {
            $substepIdentifier = $substep->getIdentifier();

            $cssBlocks[] = <<<CSS
form[name="$formName"]:not([fz-substep="$substepIdentifier"]) [fz-substep="$substepIdentifier"] {
    display: none;
}
CSS;
        }

        return $cssBlocks;
    }
}
