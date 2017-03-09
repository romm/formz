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

use Romm\Formz\Core\Core;
use Romm\Formz\Exceptions\ContextNotFoundException;
use Romm\Formz\Service\ViewHelper\FieldViewHelperService;
use Romm\Formz\Service\ViewHelper\SlotViewHelperService;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;

/**
 * Will check if a given slot has been defined.
 *
 * @see \Romm\Formz\ViewHelpers\SlotViewHelper
 */
class HasViewHelper extends AbstractConditionViewHelper implements CompilableInterface
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
        $this->registerArgument('slot', 'string', 'Name of the slot.', true);
    }

    /**
     * @inheritdoc
     *
     * @throws ContextNotFoundException
     */
    protected function callRenderMethod()
    {
        if (false === $this->fieldService->fieldContextExists()) {
            throw ContextNotFoundException::slotHasViewHelperFieldContextNotFound();
        }

        return parent::callRenderMethod();
    }

    /**
     * @param array $arguments
     * @return bool
     */
    protected static function evaluateCondition($arguments = null)
    {
        /** @var SlotViewHelperService $slotService */
        $slotService = Core::instantiate(SlotViewHelperService::class);
        $slotName = $arguments['slot'];

        return $slotService->hasSlotClosure($slotName);
    }

    /**
     * @param FieldViewHelperService $service
     */
    public function injectFieldService(FieldViewHelperService $service)
    {
        $this->fieldService = $service;
    }
}
