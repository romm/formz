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

namespace Romm\Formz\ViewHelpers\Service;

use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\ViewHelpers\FieldViewHelper;
use Romm\Formz\ViewHelpers\FormViewHelper;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * This class contains methods that help ViewHelpers to manipulate data and know
 * more things concerning the current form state.
 */
class FormzViewHelperService implements SingletonInterface
{

    /**
     * @var FormzViewHelperService
     */
    protected static $instance;

    /**
     * @return FormzViewHelperService
     */
    public static function get()
    {
        if (null === self::$instance) {
            self::$instance = GeneralUtility::makeInstance(self::class);
        }

        return self::$instance;
    }

    /**
     * Returns `true` if the `FormViewHelper` context exists.
     *
     * @return bool
     */
    public function formContextExists()
    {
        return null !== FormViewHelper::getVariable(FormViewHelper::FORM_VIEW_HELPER);
    }

    /**
     * Returns `true` if the form was submitted by the user.
     *
     * @return bool
     */
    public function formWasSubmitted()
    {
        return true === FormViewHelper::getVariable(FormViewHelper::FORM_WAS_SUBMITTED);
    }

    /**
     * Checks that the `FieldViewHelper` has been called. If not, an exception
     * is thrown.
     *
     * @param RenderingContextInterface $renderingContext
     * @return bool
     */
    public function fieldContextExists(RenderingContextInterface $renderingContext)
    {
        return null !== $this->getCurrentField($renderingContext);
    }

    /**
     * Returns the current field which was defined by the `FieldViewHelper`.
     *
     * Returns null if no current field was found.
     *
     * @param RenderingContextInterface $renderingContext
     * @return Field|null
     */
    public function getCurrentField(RenderingContextInterface $renderingContext)
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
