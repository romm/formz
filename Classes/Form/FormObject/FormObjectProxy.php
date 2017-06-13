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

namespace Romm\Formz\Form\FormObject;

use Romm\Formz\Core\Core;
use Romm\Formz\Domain\Model\FormMetadata;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObject\Service\FormObjectMetadata;
use Romm\Formz\Form\FormObject\Service\FormObjectRequestData;
use Romm\Formz\Service\HashService;

class FormObjectProxy
{
    /**
     * @var FormObject
     */
    protected $formObject;

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var string
     */
    protected $formHash;

    /**
     * @var bool
     */
    protected $formWasSubmitted = false;

    /**
     * @var bool
     */
    protected $formWasValidated = false;
    /**
     * @var FormResult
     */
    protected $formResult;

    /**
     * @var FormObjectRequestData
     */
    protected $requestData;

    /**
     * @var FormObjectMetadata
     */
    protected $metadata;

    /**
     * @var bool
     */
    protected $formIsPersistent = false;

    /**
     * @param FormObject    $formObject
     * @param FormInterface $form
     */
    public function __construct(FormObject $formObject, FormInterface $form)
    {
        $this->formObject = $formObject;
        $this->form = $form;
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Will mark the form as submitted (change the result returned by the
     * function `formWasSubmitted()`).
     *
     * @internal
     */
    public function markFormAsSubmitted()
    {
        $this->formWasSubmitted = true;
    }

    /**
     * Returns `true` if the form was submitted by the user.
     *
     * @return bool
     */
    public function formWasSubmitted()
    {
        return $this->formWasSubmitted;
    }

    /**
     * Marks the form as validated.
     *
     * @internal
     */
    public function markFormAsValidated()
    {
        $this->formWasValidated = true;
    }

    /**
     * @return bool
     */
    public function formWasValidated()
    {
        return $this->formWasValidated;
    }

    /**
     * @internal
     */
    public function markFormAsPersistent()
    {
        $this->formIsPersistent = true;
    }

    /**
     * @return bool
     */
    public function formIsPersistent()
    {
        return $this->formIsPersistent;
    }

    /**
     * @return FormResult
     */
    public function getFormResult()
    {
        if (null === $this->formResult) {
            $this->formResult = new FormResult;
        }

        return $this->formResult;
    }

    /**
     * @return FormObjectRequestData
     */
    public function getRequestData()
    {
        return $this->requestData;
    }

    /**
     * @return FormMetadata
     */
    public function getFormMetadata()
    {
        if (null === $this->metadata) {
            $this->metadata = Core::instantiate(FormObjectMetadata::class, $this->formObject);
        }

        return $this->metadata->getMetadata();
    }

    /**
     * @return string
     */
    public function getFormHash()
    {
        if (null === $this->formHash) {
            $this->formHash = HashService::get()->getHash(uniqid(get_class($this->form)));
        }

        return $this->formHash;
    }

    /**
     * @param string $hash
     */
    public function setFormHash($hash)
    {
        $this->formHash = $hash;
    }

    /**
     * @param FormObjectRequestData $requestData
     */
    public function injectRequestData(FormObjectRequestData $requestData)
    {
        $this->requestData = $requestData;
    }
}
