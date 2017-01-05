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

namespace Romm\Formz\Utility;

use Romm\Formz\Form\FormInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\Request;

/**
 * Contains features which can be useful in third party extensions.
 *
 * You can then use the following features in your scripts:
 *
 * - Access to the last submitted form which validation failed:
 *   `$myFailedForm = FormUtility::getFormWithErrors(MyForm::class);`
 *
 * - Automatic redirect if required argument is missing:
 *
 * @see `onRequiredArgumentIsMissing()`
 */
class FormUtility implements SingletonInterface
{

    /**
     * @var FormInterface[]
     */
    private static $formWithErrors = [];

    /**
     * Use this function to check if an action's required argument is missing.
     *
     * Basically, this can be used to prevent user from refreshing a page where
     * a form has been submitted, but without sending the form again: the
     * request calls the form submission controller action with an empty form
     * object, which can result in a fatal error. You can use this function to
     * prevent this kind of behaviour. Example:
     *
     * A controller has an action like:
     *  public function submitFormAction(MyForm $myForm) { ... }
     *
     * Add a new function:
     *  public function initializeSubmitFormAction()
     *  {
     *      FormUtility::onRequiredArgumentIsMissing(
     *          $this->arguments,
     *          $this->request,
     *          function($missingArgumentName) {
     *              $this->redirect('myIndex');
     *          }
     *      );
     *  }
     *
     * @param Arguments $arguments Arguments of the method arguments.
     * @param Request   $request   Request.
     * @param callable  $callback  Callback function which is called if a required argument is missing.
     */
    public static function onRequiredArgumentIsMissing($arguments, $request, $callback)
    {
        foreach ($arguments as $argument) {
            $argumentName = $argument->getName();
            if (false === $request->hasArgument($argumentName)
                && true === $argument->isRequired()
            ) {
                if (is_callable($callback)) {
                    $callback($argument->getName());
                }
            }
        }
    }

    /**
     * If a form has been submitted with errors, use this function to get it
     * back.
     * This is useful because an action is forwarded if the submitted argument
     * has errors.
     *
     * @param string $formClassName The class name of the form.
     * @return FormInterface|null
     */
    public static function getFormWithErrors($formClassName)
    {
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
