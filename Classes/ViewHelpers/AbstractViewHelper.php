<?php
/*
 * 2016 Romain CANON <romain.hydrocanon@gmail.com>
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

use Romm\Formz\Configuration\Form\Field\Field;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

abstract class AbstractViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Checks that the current `FormViewHelper` exists. If not, an exception is
     * thrown.
     *
     * @throws \Exception
     */
    public static function checkIsInsideFormViewHelper()
    {
        if (null === FormViewHelper::getVariable(FormViewHelper::FORM_VIEW_HELPER)) {
            throw new \Exception('The view helper "' . get_called_class() . '" must be used inside the view helper "' . FormViewHelper::class . '".', 1465243085);
        }
    }

    /**
     * Checks that the `FieldViewHelper` has been called. If not, an exception
     * is thrown.
     *
     * @param RenderingContextInterface $renderingContext
     * @throws \Exception
     */
    public static function checkIsInsideFieldViewHelper(RenderingContextInterface $renderingContext)
    {
        if (null === self::getCurrentField($renderingContext)) {
            throw new \Exception('The view helper "' . get_called_class() . '" must be used inside the view helper "' . FieldViewHelper::class . '".', 1465243085);
        }
    }

    /**
     * Returns the current field which was defined by the `FieldViewHelper`.
     *
     * Returns null if no current field was found.
     *
     * @param RenderingContextInterface $renderingContext
     * @return Field|null
     */
    public static function getCurrentField(RenderingContextInterface $renderingContext)
    {
        $result = null;
        $viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();

        if (true === $viewHelperVariableContainer->exists(FieldViewHelper::class, FieldViewHelper::FIELD_INSTANCE)) {
            $fieldInstance = $viewHelperVariableContainer->get(FieldViewHelper::class, FieldViewHelper::FIELD_INSTANCE);

            if ($fieldInstance instanceof Field) {
                $result = $fieldInstance;
            }
        }

        return $result;
    }
}
