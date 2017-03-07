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

namespace Romm\Formz\Service\ViewHelper;

use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Form\FormObject;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * This class contains methods that help view helpers to manipulate data and
 * know more things concerning the current form state.
 *
 * It is mainly configured inside the `FormViewHelper`, and used in other
 * view helpers.
 */
class FormViewHelperService implements SingletonInterface
{
    /**
     * @var bool
     */
    protected $formContext = false;

    /**
     * @var FormObject
     */
    protected $formObject;

    /**
     * Reset every state that can be used by this service.
     */
    public function resetState()
    {
        $this->formContext = false;
        $this->formObject = null;
    }

    /**
     * Will activate the form context, changing the result returned by the
     * function `formContextExists()`.
     *
     * @return FormViewHelperService
     * @throws DuplicateEntryException
     */
    public function activateFormContext()
    {
        if (true === $this->formContext) {
            throw DuplicateEntryException::duplicatedFormContext();
        }

        $this->formContext = true;

        return $this;
    }

    /**
     * Returns `true` if the `FormViewHelper` context exists.
     *
     * @return bool
     */
    public function formContextExists()
    {
        return $this->formContext;
    }

    /**
     * @return FormObject
     */
    public function getFormObject()
    {
        return $this->formObject;
    }

    /**
     * @param FormObject $formObject
     */
    public function setFormObject(FormObject $formObject)
    {
        $this->formObject = $formObject;
    }
}
