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
use Romm\Formz\Form\FormInterface;

interface PersistenceInterface
{
    /**
     * @return void
     */
    public function initialize();

    /**
     * Checks if the form instance determined by the identifier object exists in
     * the persistence.
     *
     * @param FormMetadata $metadata
     * @return bool
     */
    public function has(FormMetadata $metadata);

    /**
     * Fetches and returns the form instance, determined by the identifier
     * object.
     *
     * @param FormMetadata $metadata
     * @return FormInterface
     */
    public function fetch(FormMetadata $metadata);

    /**
     * Saves the given form instance in the persistence.
     *
     * @param FormMetadata $metadata
     * @param FormInterface        $form
     * @return void
     */
    public function save(FormMetadata $metadata, FormInterface $form);

    /**
     * Deletes the form instance from the persistence.
     *
     * @param FormMetadata $metadata
     * @return void
     */
    public function delete(FormMetadata $metadata);

    /**
     * Returns the priority of the middleware. The highest the priority is, the
     * sooner the persistence will be called.
     *
     * @return int
     */
    public function getPriority();
}
