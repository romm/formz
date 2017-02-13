<?php
/*
 * 2017 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Formz project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\ViewHelpers;

use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;

abstract class AbstractViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * @return VariableProviderInterface|RenderingContext
     */
    protected function getVariableProvider()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '8.0.0', '>='))
            ? $this->renderingContext->getVariableProvider()
            : $this->renderingContext->getTemplateVariableContainer();
    }
}
