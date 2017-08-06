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

namespace Romm\Formz\Service;

use Romm\Formz\Form\FormInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Contains features which can be useful in third party extensions.
 *
 * You can then use the following features in your scripts:
 *
 * - Access to the last submitted form which validation failed:
 *   `$myFailedForm = FormUtility::getFormWithErrors(MyForm::class);`
 */
class FormService implements SingletonInterface
{

    /**
     * @var FormInterface[]
     */
    private static $formWithErrors = [];

    /**
     * If a form has been submitted with errors, use this function to get it
     * back.
     * This is useful because an action is forwarded if the submitted argument
     * has errors.
     *
     * @deprecated This method is deprecated, please try not to use it if you
     *             can. It will be removed in FormZ v2, where you will have a
     *             whole new way to get a validated form.
     *
     * @param string $formClassName The class name of the form.
     * @return FormInterface|null
     */
    public static function getFormWithErrors($formClassName)
    {
        GeneralUtility::logDeprecatedFunction();

        return (isset(self::$formWithErrors[$formClassName]))
            ? self::$formWithErrors[$formClassName]
            : null;
    }

    /**
     * If a form validation has failed, this function is used to save it for
     * further usage.
     *
     * @param FormInterface $form
     * @internal
     */
    public static function addFormWithErrors(FormInterface $form)
    {
        self::$formWithErrors[get_class($form)] = $form;
    }
}
