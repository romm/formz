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

namespace Romm\Formz\Middleware\Item;

use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorInterface;
use Romm\Formz\Exceptions\InvalidArgumentValueException;
use Romm\Formz\Exceptions\InvalidEntryException;
use Romm\Formz\Exceptions\MissingArgumentException;
use Romm\Formz\Exceptions\SignalNotFoundException;
use Romm\Formz\Form\Definition\Step\Step\Step;
use Romm\Formz\Form\Definition\Middleware\MiddlewareScopes;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Middleware\Item\Step\Service\StepMiddlewareService;
use Romm\Formz\Middleware\MiddlewareInterface;
use Romm\Formz\Middleware\Option\AbstractOptionDefinition;
use Romm\Formz\Middleware\Option\OptionInterface;
use Romm\Formz\Middleware\Processor\MiddlewareProcessor;
use Romm\Formz\Middleware\Request\Forward;
use Romm\Formz\Middleware\Request\Redirect;
use Romm\Formz\Middleware\Scope\MainScope;
use Romm\Formz\Middleware\Signal\After;
use Romm\Formz\Middleware\Signal\Before;
use Romm\Formz\Middleware\Signal\MiddlewareSignal;
use Romm\Formz\Middleware\Signal\SendsMiddlewareSignal;
use Romm\Formz\Middleware\Signal\SignalObject;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\Web\Request;

/**
 * Abstract class that must be extended by middlewares.
 *
 * Child middleware must implement their own signals.
 */
abstract class AbstractMiddleware implements MiddlewareInterface, DataPreProcessorInterface
{
    /**
     * @var MiddlewareProcessor
     */
    private $processor;

    /**
     * This is the default option class, this property can be overridden in
     * child classes to be mapped to another option definition.
     *
     * @var \Romm\Formz\Middleware\Option\DefaultOptionDefinition
     */
    protected $options;

    /**
     * @var \Romm\Formz\Form\Definition\Middleware\MiddlewareScopes
     */
    protected $scopes = [];

    /**
     * @var array
     */
    protected static $defaultScopesWhiteList = [];

    /**
     * @var array
     */
    protected static $defaultScopesBlackList = [];

    /**
     * Can be overridden in child class with custom priority value.
     *
     * The higher the priority is, the earlier the middleware is called.
     *
     * Note that you can also override the method `getPriority()` for advanced
     * priority calculation.
     *
     * @var int
     */
    protected $priority = 0;

    /**
     * @param OptionInterface  $options
     * @param MiddlewareScopes $scopes
     */
    final public function __construct(OptionInterface $options, MiddlewareScopes $scopes)
    {
        $this->options = $options;
        $this->scopes = $scopes;
    }

    /**
     * Abstraction for processing the middleware initialization.
     *
     * For own initialization, @see initializeMiddleware()
     */
    final public function initialize()
    {
        $this->initializeMiddleware();
    }

    /**
     * You can override this method in your child class to initialize your
     * middleware correctly.
     */
    protected function initializeMiddleware()
    {
    }

    /**
     * @see \Romm\Formz\Middleware\Signal\SendsMiddlewareSignal::beforeSignal()
     *
     * @param string $signal
     * @return SignalObject
     */
    final public function beforeSignal($signal = null)
    {
        return $this->getSignalObject($signal, Before::class);
    }

    /**
     * @see \Romm\Formz\Middleware\Signal\SendsMiddlewareSignal::afterSignal()
     *
     * @param string $signal
     * @return SignalObject
     */
    final public function afterSignal($signal = null)
    {
        return $this->getSignalObject($signal, After::class);
    }

    /**
     * @return AbstractOptionDefinition
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @todo
     */
    protected function redirectToNextStep()
    {
        $formObject = $this->getFormObject();

        if ($formObject->hasForm()
            && $formObject->isPersistent()
        ) {
            $formObject->getFormMetadata()->persist();
        }

        $service = StepMiddlewareService::get();
        $nextStep = $service->getNextStep($this->getCurrentStep());

        if ($nextStep) {
            $service->moveForwardToStep($nextStep, $this->redirect());
        }
    }

    /**
     * @return MiddlewareScopes
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * Returns a new forward dispatcher, on which you can add options by calling
     * its fluent methods.
     *
     * You must call the method `dispatch()` to actually dispatch the forward
     * signal.
     *
     * @return Forward
     */
    final protected function forward()
    {
        return new Forward($this->getRequest(), $this->getFormObject());
    }

    /**
     * Returns a new redirect dispatcher, on which you can add options by
     * calling its fluent methods.
     *
     * You must call the method `dispatch()` to actually dispatch the redirect
     * signal.
     *
     * @return Redirect
     */
    final protected function redirect()
    {
        return new Redirect($this->getRequest(), $this->getFormObject());
    }

    /**
     * @return FormObject
     */
    final protected function getFormObject()
    {
        return $this->processor->getFormObject();
    }

    /**
     * @return Request
     */
    final protected function getRequest()
    {
        return $this->processor->getRequest();
    }

    /**
     * @return Arguments
     */
    final protected function getRequestArguments()
    {
        return $this->processor->getRequestArguments();
    }

    /**
     * @return array
     */
    final protected function getSettings()
    {
        return $this->processor->getSettings();
    }

    /**
     * @return Step|null
     */
    final protected function getCurrentStep()
    {
        return $this->getFormObject()->getCurrentStep();
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return (int)$this->priority;
    }

    /**
     * @param MiddlewareProcessor $middlewareProcessor
     */
    final public function bindMiddlewareProcessor(MiddlewareProcessor $middlewareProcessor)
    {
        $this->processor = $middlewareProcessor;
    }

    /**
     * Returns the name of the signal on which this middleware is bound.
     *
     * @return string
     * @throws SignalNotFoundException
     */
    final public function getBoundSignalName()
    {
        $interfaces = class_implements($this);

        foreach ($interfaces as $interface) {
            if (in_array(MiddlewareSignal::class, class_implements($interface))) {
                return $interface;
            }
        }

        throw SignalNotFoundException::signalNotFoundInMiddleware($this);
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

        if (false === isset($data['scopes'])) {
            $data['scopes'] = [];
        }

        if (false === isset($data['scopes']['whiteList'])) {
            $data['scopes']['whiteList'] = [MainScope::class];
        }

        if (false === isset($data['scopes']['blackList'])) {
            $data['scopes']['blackList'] = [];
        }

        $data['scopes']['whiteList'] = array_unique(array_merge(static::$defaultScopesWhiteList, $data['scopes']['whiteList']));
        $data['scopes']['blackList'] = array_unique(array_merge(static::$defaultScopesBlackList, $data['scopes']['blackList']));

        $processor->setData($data);
    }

    /**
     * @param string $signal
     * @param string $type
     * @return SignalObject
     * @throws InvalidArgumentValueException
     * @throws InvalidEntryException
     * @throws MissingArgumentException
     */
    private function getSignalObject($signal, $type)
    {
        if (false === $this instanceof SendsMiddlewareSignal) {
            throw InvalidEntryException::middlewareNotSendingSignals($this);
        }

        /** @var SendsMiddlewareSignal $this */
        if (null === $signal) {
            if (count($this->getAllowedSignals()) > 1) {
                throw MissingArgumentException::signalNameArgumentMissing($this);
            }

            $signal = reset($this->getAllowedSignals());
        }

        if (false === in_array($signal, $this->getAllowedSignals())) {
            throw InvalidArgumentValueException::signalNotAllowed($this);
        }

        return new SignalObject($this->processor, $signal, $type);
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return ['options', 'scopes'];
    }
}
