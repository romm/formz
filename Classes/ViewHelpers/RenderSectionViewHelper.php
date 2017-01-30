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

use Romm\Formz\Core\Core;
use Romm\Formz\ViewHelpers\Service\FieldService;
use Romm\Formz\ViewHelpers\Service\SectionService;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;

/**
 * This is the rendering function for the `section` view helper.
 *
 * @see \Romm\Formz\ViewHelpers\SectionViewHelper
 */
class RenderSectionViewHelper extends AbstractViewHelper implements CompilableInterface
{
    /**
     * @var FieldService
     */
    protected $fieldService;

    /**
     * @inheritdoc
     */
    public function initializeArguments()
    {
        $this->registerArgument('section', 'object', 'Instance of the section which will be rendered.', true);
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        $this->fieldService->checkIsInsideFieldViewHelper();

        return self::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
    }

    /**
     * Will render the section with the given name, only if the section is
     * found.
     *
     * @inheritdoc
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        /** @var SectionService $sectionService */
        $sectionService = Core::instantiate(SectionService::class);

        $closure = $sectionService->getSectionClosure($arguments['section']);

        return (null !== $closure)
            ? $closure()
            : '';
    }

    /**
     * @param FieldService $service
     */
    public function injectFieldService(FieldService $service)
    {
        $this->fieldService = $service;
    }
}
