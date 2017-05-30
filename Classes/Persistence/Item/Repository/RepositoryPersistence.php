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

namespace Romm\Formz\Persistence\Item\Repository;

use Romm\Formz\Core\Core;
use Romm\Formz\Domain\Model\FormMetadata;
use Romm\Formz\Exceptions\InvalidArgumentTypeException;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Persistence\AbstractPersistence;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;

class RepositoryPersistence extends AbstractPersistence
{
    /**
     * @var \Romm\Formz\Persistence\Item\Repository\RepositoryPersistenceOption
     */
    protected $options;

    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * Creates the repository instance.
     */
    public function initialize()
    {
        $this->repository = Core::instantiate($this->options->getRepositoryClassName());
        $this->persistenceManager = Core::instantiate(PersistenceManager::class);
    }

    /**
     * Checks that the form instance that matches the identifier exists in the
     * database.
     *
     * @param FormMetadata $metadata
     * @return bool
     */
    public function has(FormMetadata $metadata)
    {
        $object = $metadata->hasIdentifier()
            ? $this->repository->findByUid($metadata->getIdentifier())
            : null;

        return $object !== null;
    }

    /**
     * Returns the form instance that matches the identifier. If it does not
     * exist, an exception is thrown.
     *
     * @param FormMetadata $metadata
     * @return FormInterface
     */
    public function fetch(FormMetadata $metadata)
    {
        $this->checkInstanceCanBeFetched($metadata);

        /** @var FormInterface $object */
        $object = $this->repository->findByUid($metadata->getIdentifier());

        return $object;
    }

    /**
     * Saves the form instance in the database.
     *
     * If the given form instance is not an instance of `DomainObjectInterface`,
     * an exception is thrown.
     *
     * @param FormMetadata $metadata
     * @param FormInterface        $form
     * @throws InvalidArgumentTypeException
     */
    public function save(FormMetadata $metadata, FormInterface $form)
    {
        if (false === $form instanceof DomainObjectInterface) {
            throw InvalidArgumentTypeException::persistenceRepositoryWrongFormType($form);
        }

        /** @var DomainObjectInterface $form */
        $new = null === $form->getUid();

        if ($new) {
            $this->repository->add($form);
        } else {
            $this->repository->update($form);
        }

        $this->persistenceManager->persistAll();

        if ($new) {
            $metadata->setIdentifier($form->getUid());
        }
    }

    /**
     * Removes the given entry from database.
     *
     * @param FormMetadata $metadata
     */
    public function delete(FormMetadata $metadata)
    {
        if ($this->has($metadata)) {
            $this->repository->remove($this->fetch($metadata));

            $this->persistenceManager->persistAll();
        }
    }
}
