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

namespace Romm\Formz\Middleware\Item\Persistence;

use Romm\Formz\Domain\Model\FormMetadata;
use Romm\Formz\Domain\Repository\FormMetadataRepository;
use Romm\Formz\Middleware\Argument\Arguments;
use Romm\Formz\Middleware\Item\AbstractMiddleware;
use Romm\Formz\Middleware\Item\FormInjection\FormInjectionSignal;
use Romm\Formz\Middleware\Processor\PresetMiddlewareInterface;
use Romm\Formz\Middleware\Signal\Before;
use Romm\Formz\Middleware\Signal\SendsMiddlewareSignal;

/**
 * This middleware will try to fetch a form instance using the persistence
 * manager.
 *
 * If a form hash is found, it is used to fetch for a form metadata instance in
 * database. If the metadata if found, its data are used to search for a form
 * instance in every persistence service bound to the form object.
 *
 * The form hash can be retrieved either in the request arguments, or in the
 * option object of this middleware: @see PersistenceFetchingMiddlewareOption::setFormHash()
 */
class PersistenceFetchingMiddleware extends AbstractMiddleware implements Before, FormInjectionSignal, SendsMiddlewareSignal, PresetMiddlewareInterface
{
    /**
     * @var \Romm\Formz\Middleware\Item\Persistence\PersistenceFetchingMiddlewareOption
     */
    protected $options;

    /**
     * @var FormMetadataRepository
     */
    protected $metadataRepository;

    /**
     * @see PersistenceFetchingMiddleware
     *
     * @param Arguments $arguments
     */
    public function before(Arguments $arguments)
    {
        $formObject = $this->getFormObject();

        if (false === $formObject->getDefinition()->hasPersistence()) {
            return;
        }

        $this->beforeSignal()->dispatch();

        /*
         * If the form has already been injected in the form object, we do not
         * need to try to fetch it from persistence.
         */
        if ($formObject->hasForm()) {
            return;
        }

        $hash = $this->getFormHash();
        $metadata = null;
        $form = null;

        if ($hash) {
            $metadata = $this->getMetadata($hash);

            if ($metadata) {
                $form = $formObject->getPersistenceManager()->fetchFirst($metadata);
            }
        }

        $this->afterSignal()
            ->withArguments(new PersistenceFetchingArguments($hash, $metadata, $form))
            ->dispatch();
    }

    /**
     * This function tries to fetch the form hash for the current form:
     * - First, in the options of this middleware;
     * - Then, in the arguments of the request.
     *
     * If no hash is found, `null` is returned.
     *
     * @return string|null
     */
    protected function getFormHash()
    {
        if (null !== $this->options->getFormHash()) {
            return $this->options->getFormHash();
        }

        if ($this->getRequest()->hasArgument('fz-hash')) {
            $formName = $this->getFormObject()->getName();
            $identifierList = $this->getRequest()->getArgument('fz-hash');

            if (is_array($identifierList)
                && isset($identifierList[$formName])
            ) {
                return (string)$identifierList[$formName];
            }
        }

        return null;
    }

    /**
     * Fetches the metadata bound to the given hash.
     *
     * if the metadata is not found, or if the metadata class name does not
     * match the form object class name, `null` is returned.
     *
     * @param string $hash
     * @return FormMetadata|null
     */
    protected function getMetadata($hash)
    {
        $metadata = $this->metadataRepository->findOneByHash($hash);

        if ($metadata
            && $metadata->getClassName() !== $this->getFormObject()->getClassName()
        ) {
            $metadata = null;
        }

        return $metadata;
    }

    /**
     * @param FormMetadataRepository $metadataRepository
     */
    public function injectMetadataRepository(FormMetadataRepository $metadataRepository)
    {
        $this->metadataRepository = $metadataRepository;
    }

    /**
     * @return array
     */
    public function getAllowedSignals()
    {
        return [PersistenceFetchingSignal::class];
    }
}
