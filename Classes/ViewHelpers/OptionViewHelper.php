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
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;

/**
 * Use this view helper at the first level inside the Field view helper. It will
 * be added to the arguments list, which can then be used inside the field
 * template.
 *
 * It does not add any feature, but allows the Fluid template to be far more
 * readable, as you can see below:
 *
 * Without the Option view helper:
 *
 * ```
 *  <formz:field layout="..."
 *               arguments="{label: '{f:translate(key: \'my_lll_key\')}', foo: 'bar'}">
 *
 *      ...
 *  </formz:field>
 * ```
 *
 * With it:
 *
 * ```
 *  <formz:field layout="...">
 *      <formz:option name="label" value="{f:translate(key: 'my_lll_key')}" />
 *      <formz:option name="foo" value="bar" />
 *
 *      ...
 *  </formz:field>
 * ```
 */
class OptionViewHelper extends AbstractViewHelper implements CompilableInterface
{
    /**
     * @var FieldViewHelperService
     */
    protected $fieldService;

    /**
     * @var array
     */
    protected static $options = [];

    /**
     * @inheritdoc
     */
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Name of the option.', true);
        $this->registerArgument('value', 'string', 'Value of the option.', true);
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        if (false === $this->fieldService->fieldContextExists()) {
            throw new ContextNotFoundException(
                'The view helper "' . get_called_class() . '" must be used inside the view helper "' . FieldViewHelper::class . '".',
                1465243085
            );
        }

        return self::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
    }

    /**
     * @inheritdoc
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        /** @var FieldViewHelperService $service */
        $service = Core::instantiate(FieldViewHelperService::class);

        $service->setFieldOption($arguments['name'], $arguments['value']);
    }

    /**
     * @param FieldViewHelperService $service
     */
    public function injectFieldService(FieldViewHelperService $service)
    {
        $this->fieldService = $service;
    }
}
