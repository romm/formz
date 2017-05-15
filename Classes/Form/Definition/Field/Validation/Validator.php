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

namespace Romm\Formz\Form\Definition\Field\Validation;

use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorInterface;
use Romm\ConfigurationObject\Traits\ConfigurationObject\ArrayConversionTrait;
use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Exceptions\SilentException;
use Romm\Formz\Form\Definition\AbstractFormDefinitionComponent;
use Romm\Formz\Form\Definition\Condition\Activation;
use Romm\Formz\Form\Definition\Condition\ActivationInterface;
use Romm\Formz\Form\Definition\Condition\ActivationUsageInterface;
use Romm\Formz\Form\Definition\Field\Field;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Validator extends AbstractFormDefinitionComponent implements ActivationUsageInterface, DataPreProcessorInterface
{
    use ArrayConversionTrait;

    /**
     * @var string
     * @validate NotEmpty
     */
    private $name;

    /**
     * @var string
     * @validate NotEmpty
     * @validate Romm.ConfigurationObject:ClassImplements(interface=TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface)
     */
    private $className;

    /**
     * @var int
     * @validate Number
     */
    protected $priority;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var \Romm\Formz\Form\Definition\Field\Validation\Message[]
     */
    protected $messages = [];

    /**
     * @var \Romm\Formz\Form\Definition\Condition\Activation
     * @validate Romm.Formz:Internal\ConditionIsValid
     */
    protected $activation;

    /**
     * @var bool
     */
    protected $useAjax = false;

    /**
     * @param string $name
     * @param string $className
     */
    public function __construct($name, $className)
    {
        $this->name = $name;
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority($priority)
    {
        $this->checkDefinitionFreezeState();

        $this->priority = $priority;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->checkDefinitionFreezeState();

        $this->options = $options;
    }

    /**
     * @return Message[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param string $identifier
     * @return bool
     */
    public function hasMessage($identifier)
    {
        return true === isset($this->messages[$identifier]);
    }

    /**
     * @param string $identifier
     * @return Message
     * @throws EntryNotFoundException
     */
    public function getMessage($identifier)
    {
        if (false === $this->hasMessage($identifier)) {
            throw EntryNotFoundException::messageNotFound($identifier);
        }

        return $this->messages[$identifier];
    }

    /**
     * @param string $identifier
     * @return Message
     * @throws DuplicateEntryException
     */
    public function addMessage($identifier)
    {
        $this->checkDefinitionFreezeState();

        if ($this->hasMessage($identifier)) {
            throw DuplicateEntryException::validatorMessageAlreadyAdded($identifier, $this);
        }

        /** @var Message $message */
        $message = GeneralUtility::makeInstance(Message::class, $identifier);
        $message->attachParent($this);

        $this->messages[$identifier] = $message;

        return $message;
    }

    /**
     * @return ActivationInterface
     * @throws SilentException
     */
    public function getActivation()
    {
        if (false === $this->hasActivation()) {
            throw SilentException::validatorHasNoActivation($this);
        }

        return $this->activation;
    }

    /**
     * @return bool
     */
    public function hasActivation()
    {
        return $this->activation instanceof ActivationInterface;
    }

    /**
     * @return ActivationInterface
     */
    public function addActivation()
    {
        $this->checkDefinitionFreezeState();

        if (null === $this->activation) {
            $this->activation = GeneralUtility::makeInstance(Activation::class);
            $this->activation->attachParent($this);
        }

        return $this->activation;
    }

    /**
     * @return bool
     */
    public function doesUseAjax()
    {
        return (bool)$this->useAjax;
    }

    /**
     * Turns the Ajax usage ON.
     */
    public function activateAjaxUsage()
    {
        $this->checkDefinitionFreezeState();

        $this->useAjax = true;
    }

    /**
     * Turns the Ajax usage OFF.
     */
    public function deactivateAjaxUsage()
    {
        $this->checkDefinitionFreezeState();

        $this->useAjax = false;
    }

    /**
     * @return Field
     */
    public function getParentField()
    {
        /** @var Field $field */
        $field = $this->getFirstParent(Field::class);

        return $field;
    }

    /**
     * @param DataPreProcessor $processor
     */
    public static function dataPreProcessor(DataPreProcessor $processor)
    {
        $data = $processor->getData();

        /*
         * Forcing the identifiers of the messages: they are the keys of the
         * array entries.
         */
        self::forceNameForProperty($data, 'messages', 'identifier');

        $processor->setData($data);
    }
}
