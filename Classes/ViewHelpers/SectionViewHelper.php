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
use Romm\Formz\Exceptions\ContextNotFoundException;
use Romm\Formz\Service\ViewHelper\FieldViewHelperService;
use Romm\Formz\Service\ViewHelper\SectionViewHelperService;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;

/**
 * This view helper registers a section which will not be rendered directly, but
 * with the usage of the `RenderSection` view helper.
 *
 * It is used to manage dynamic parts of the layouts used with the `field` view
 * helper: every layout can call as many sections as it needs, and this sections
 * must then be registered using this view helper.
 */
class SectionViewHelper extends AbstractViewHelper implements CompilableInterface
{
    /**
     * @var FieldViewHelperService
     */
    protected $fieldService;

    /**
     * @inheritdoc
     */
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Name of the section.', true);
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        if (false === $this->fieldService->fieldContextExists()) {
            throw new ContextNotFoundException(
                'The view helper "' . get_called_class() . '" must be used inside the view helper "' . FieldViewHelper::class . '".',
                1488474106
            );
        }

        return self::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
    }

    /**
     * @inheritdoc
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        /** @var SectionViewHelperService $sectionService */
        $sectionService = Core::instantiate(SectionViewHelperService::class);

        $sectionService->addSectionClosure($arguments['name'], $renderChildrenClosure);
    }

    /**
     * @param FieldViewHelperService $service
     */
    public function injectFieldService(FieldViewHelperService $service)
    {
        $this->fieldService = $service;
    }
}
