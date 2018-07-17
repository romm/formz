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

namespace Romm\Formz\ViewHelpers\Slot;

use Closure;
use Romm\Formz\Core\Core;
use Romm\Formz\Exceptions\ContextNotFoundException;
use Romm\Formz\Service\ViewHelper\Field\FieldViewHelperService;
use Romm\Formz\Service\ViewHelper\Slot\SlotViewHelperService;
use Romm\Formz\ViewHelpers\AbstractViewHelper;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;

/**
 * This is the rendering function for the `slot` view helper.
 *
 * @see \Romm\Formz\ViewHelpers\SlotViewHelper
 */
class RenderViewHelper extends AbstractViewHelper implements CompilableInterface
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @inheritdoc
     */
    public function initializeArguments()
    {
        $this->registerArgument('slot', 'string', 'Instance of the slot which will be rendered.', true);
        $this->registerArgument('arguments', 'array', 'Arguments sent to the slot.', false, []);
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        parent::initializeArguments();

        return self::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
    }

    /**
     * Will render the slot with the given name, only if the slot is found.
     *
     * @inheritdoc
     */
    public static function renderStatic(array $arguments, Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        /** @var FieldViewHelperService $fieldService */
        $fieldService = Core::instantiate(FieldViewHelperService::class);

        if (false === $fieldService->fieldContextExists()) {
            throw ContextNotFoundException::slotRenderViewHelperFieldContextNotFound();
        }

        /** @var SlotViewHelperService $slotService */
        $slotService = Core::instantiate(SlotViewHelperService::class);
        $slotName = $arguments['slot'];
        $result = '';

        if ($slotService->hasSlot($slotName)) {
            $currentVariables = version_compare(VersionNumberUtility::getCurrentTypo3Version(), '8.0.0', '<')
                ? $renderingContext->getTemplateVariableContainer()->getAll()
                : $renderingContext->getVariableProvider()->getAll();

            ArrayUtility::mergeRecursiveWithOverrule($currentVariables, $arguments['arguments']);

            $slotService->addTemplateVariables($slotName, $currentVariables);

            $slotClosure = $slotService->getSlotClosure($slotName);
            $result = $slotClosure();

            $slotService->restoreTemplateVariables($slotName);
        }

        return $result;
    }
}
