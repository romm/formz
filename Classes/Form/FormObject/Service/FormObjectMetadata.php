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

namespace Romm\Formz\Form\FormObject\Service;

use Romm\Formz\Domain\Model\DataObject\FormMetadataObject;
use Romm\Formz\Domain\Model\FormMetadata;
use Romm\Formz\Domain\Repository\FormMetadataRepository;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Form\IdentifiableFormInterface;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;

class FormObjectMetadata
{
    /**
     * @var FormObject
     */
    protected $formObject;

    /**
     * @var FormMetadata
     */
    protected $metadata;

    /**
     * @var FormMetadataRepository
     */
    protected $metadataRepository;

    /**
     * @param FormObject $formObject
     */
    public function __construct(FormObject $formObject)
    {
        $this->formObject = $formObject;
    }

    /**
     * @return FormMetadata
     */
    public function getMetadata()
    {
        if (null === $this->metadata) {
            $formHash = $this->formObject->getFormHash();
            $identifier = $this->getFormIdentifier();

            $this->metadata = ($identifier)
                ? $this->metadataRepository->findOneByClassNameAndIdentifier($this->formObject->getClassName(), $identifier)
                : $this->metadataRepository->findOneByHash($formHash);

            if (false === $this->metadata instanceof FormMetadata) {
                $this->metadata = new FormMetadata(
                    $formHash,
                    $this->formObject->getClassName(),
                    $this->getFormIdentifier(),
                    new FormMetadataObject
                );
            }
        }

        return $this->metadata;
    }

    /**
     * @return int|string
     */
    protected function getFormIdentifier()
    {
        $identifier = null;

        if ($this->formObject->hasForm()) {
            $form = $this->formObject->getForm();

            if ($form instanceof IdentifiableFormInterface) {
                $identifier = $form->getFormIdentifier();
            } elseif ($form instanceof DomainObjectInterface
                && null !== $form->getUid()
            ) {
                $identifier = $form->getUid();
            }
        }

        return $identifier;
    }

    /**
     * @param FormMetadataRepository $metadataRepository
     */
    public function injectMetadataRepository(FormMetadataRepository $metadataRepository)
    {
        $this->metadataRepository = $metadataRepository;
    }
}
