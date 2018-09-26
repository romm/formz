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
 *  <fz:field layout="..."
 *               arguments="{label: '{f:translate(key: \'my_lll_key\')}', foo: 'bar'}">
 *
 *      ...
 *  </fz:field>
 * ```
 *
 * With it:
 *
 * ```
 *  <fz:field layout="...">
 *      <fz:option name="label" value="{f:translate(key: 'my_lll_key')}" />
 *      <fz:option name="foo" value="bar" />
 *
 *      ...
 *  </fz:field>
 * ```
 */
class OptionViewHelper extends AbstractViewHelper implements CompilableInterface
{
    /**
     * @var array
     */
    protected static $options = [];

    /**
     * @inheritdoc
     */
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument('name', 'string', 'Name of the option.', true);
        $this->registerArgument('value', 'string', 'Value of the option.', true);
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
        /** @var FieldViewHelperService $service */
        $service = Core::instantiate(FieldViewHelperService::class);

        if (false === $service->fieldContextExists()) {
            throw ContextNotFoundException::optionViewHelperFieldContextNotFound();
        }

        $service->setFieldOption($arguments['name'], $arguments['value']);
    }
}
