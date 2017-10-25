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
use Romm\Formz\Exceptions\InvalidOptionValueException;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObject\FormObjectFactory;
use Romm\Formz\Service\ViewHelper\FormViewHelperService;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;

/**
 * Use this view helper to get the identifier hash of a given form.
 *
 * If you use it inside the `FormViewHelper`, you don't have to fill the
 * arguments `form` and `name`.
 */
class FormIdentifierHashViewHelper extends AbstractViewHelper implements CompilableInterface
{
    /**
     * @inheritdoc
     */
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument('form', 'object', 'Form instance, that must implement `FormInterface`.');
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
        /** @var FormViewHelperService $formService */
        $formService = Core::instantiate(FormViewHelperService::class);

        $formObject = null;
        $form = $arguments['form'];

        if (null !== $form) {
            if (false === is_object($form)) {
                throw InvalidOptionValueException::formIdentifierViewHelperWrongFormValueType($form);
            }

            if (false === $form instanceof FormInterface) {
                throw InvalidOptionValueException::formIdentifierViewHelperWrongFormValueObjectType($form);
            }

            $formObject = FormObjectFactory::get()->getInstanceWithFormInstance($form);
        } elseif ($formService->formContextExists()) {
            $formObject = $formService->getFormObject();
        }

        if (null === $formObject) {
            throw ContextNotFoundException::formIdentifierViewHelperFormContextNotFound();
        }

        return $formObject->getFormHash();
    }
}
