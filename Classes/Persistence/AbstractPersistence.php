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

use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorInterface;
use Romm\Formz\Domain\Model\FormMetadata;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Persistence\Option\AbstractOptionDefinition;

abstract class AbstractPersistence implements PersistenceInterface, DataPreProcessorInterface
{
    /**
     * @var int
     */
    protected $priority = 0;

    /**
     * This is the default option class, this property can be overridden in
     * child classes to be mapped to another option definition.
     *
     * @var \Romm\Formz\Persistence\Option\DefaultOptionDefinition
     */
    protected $options;

    /**
     * @param AbstractOptionDefinition $options
     */
    final public function __construct(AbstractOptionDefinition $options)
    {
        $this->options = $options;
    }

    /**
     * Override if needed.
     */
    public function initialize()
    {
    }

    /**
     * Returns the priority of the middleware. The higher the priority is, the
     * earlier the persistence will be called.
     *
     * @return int
     */
    public function getPriority()
    {
        return (int)$this->priority;
    }

    /**
     * Will inject empty options if no option has been defined at all.
     *
     * @param DataPreProcessor $processor
     */
    public static function dataPreProcessor(DataPreProcessor $processor)
    {
        $data = $processor->getData();

        if (false === isset($data['options'])) {
            $data['options'] = [];
        }

        $processor->setData($data);
    }

    /**
     * Checks if the instance bound to the identifier object can be fetched,
     * throws an exception otherwise.
     *
     * @param FormMetadata $metadata
     * @throws EntryNotFoundException
     */
    protected function checkInstanceCanBeFetched(FormMetadata $metadata)
    {
        if (false === $this->has($metadata)) {
            throw EntryNotFoundException::persistenceSessionEntryNotFound($metadata);
        }
    }
}
