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

namespace Romm\Formz\Persistence;

use Romm\Formz\Domain\Model\FormMetadata;
use Romm\Formz\Exceptions\InvalidEntryException;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Form\FormObject\FormObjectFactory;

/**
 * Manages persistence for a given form object.
 */
class PersistenceManager
{
    /**
     * @var FormObject
     */
    protected $formObject;

    /**
     * @var bool
     */
    protected $initializationDone = false;

    /**
     * @param FormObject $formObject
     */
    public function __construct(FormObject $formObject)
    {
        $this->formObject = $formObject;
    }

    /**
     * Loops on the registered persistence services and saves the given form
     * instance in each one.
     */
    public function save()
    {
        $this->initializePersistence();

        $form = $this->formObject->getForm();
        $proxy = FormObjectFactory::get()->getProxy($form);
        $metadata = $proxy->getFormMetadata();
        $identifier = $metadata->getIdentifier();
        $persistenceServices = $this->getSortedPersistenceServices();

        foreach ($persistenceServices as $persistence) {
            $persistence->save($metadata, $form);
        }

        if (count($persistenceServices) > 0) {
            $proxy->markFormAsPersistent();
        }

        /*
         * If the form identifier has changed during the saving process (for
         * instance the form has been saved in database and has a new uid), the
         * metadata is persisted to be sure the form identifier is saved in
         * database.
         */
        if ($identifier !== $metadata->getIdentifier()) {
            $metadata->getMetadata()->persist();
        }
    }

    /**
     * Loops on the registered persistence services, and tries to fetch the
     * form. If a form is found, it is returned and the loop breaks. If not form
     * is found, `null` is returned.
     *
     * @param FormMetadata $metadata
     * @return FormInterface|null
     * @throws InvalidEntryException
     */
    public function fetchFirst(FormMetadata $metadata)
    {
        $this->initializePersistence();

        foreach ($this->getSortedPersistenceServices() as $persistence) {
            if ($persistence->has($metadata)) {
                $form = $persistence->fetch($metadata);

                if (false === $form instanceof FormInterface) {
                    throw InvalidEntryException::persistenceInvalidEntryFetched($persistence, $form);
                }

                $this->formObject->setForm($form);

                $proxy = FormObjectFactory::get()->getProxy($form);
                $proxy->setFormHash($metadata->getHash());
                $proxy->markFormAsPersistent();

                return $form;
            }
        }

        return null;
    }

    /**
     * Loops on persistence services and initializes them.
     */
    protected function initializePersistence()
    {
        if (false === $this->initializationDone) {
            $this->initializationDone = true;

            foreach ($this->formObject->getDefinition()->getPersistence() as $persistence) {
                $persistence->initialize();
            }
        }
    }

    /**
     * Sorts the persistence services, based on their priority property: the
     * ones with the highest priority will come first.
     *
     * @return PersistenceInterface[]
     */
    protected function getSortedPersistenceServices()
    {
        $items = $this->formObject->getDefinition()->getPersistence();

        usort($items, function (PersistenceInterface $a, PersistenceInterface $b) {
            $priorityA = (int)$a->getPriority();
            $priorityB = (int)$b->getPriority();

            if ($priorityA === $priorityB) {
                return 0;
            }

            return $priorityA < $priorityB ? 1 : -1;
        });

        return $items;
    }
}
