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

namespace Romm\Formz\ViewHelpers;

use Romm\Formz\Core\Core;
use Romm\Formz\Exceptions\ContextNotFoundException;
use Romm\Formz\Service\ViewHelper\Field\FieldViewHelperService;
use Romm\Formz\Service\ViewHelper\Slot\SlotViewHelperService;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;

/**
 * This view helper registers a slot which will not be rendered directly, but
 * with the usage of the `slot.render` view helper.
 *
 * It is used to manage dynamic parts of the layouts used with the `field` view
 * helper: every layout can call as many slots as it needs, and this slots must
 * then be registered using this view helper.
 */
class SlotViewHelper extends AbstractViewHelper implements CompilableInterface
{
    /**
     * @inheritdoc
     */
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Name of the slot.', true);
        $this->registerArgument('arguments', 'array', 'Arguments sent to the slot.', false, []);
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        return self::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
    }

    /**
     * @inheritdoc
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        /** @var FieldViewHelperService $fieldService */
        $fieldService = Core::instantiate(FieldViewHelperService::class);

        if (false === $fieldService->fieldContextExists()) {
            throw ContextNotFoundException::slotViewHelperFieldContextNotFound();
        }

        /** @var SlotViewHelperService $slotService */
        $slotService = Core::instantiate(SlotViewHelperService::class);

        $slotService->addSlot($arguments['name'], $renderChildrenClosure, $arguments['arguments']);
    }
}
