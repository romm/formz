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

namespace Romm\Formz\Middleware\Processor;

use Romm\Formz\Controller\Processor\ControllerProcessor;
use Romm\Formz\Core\Core;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Middleware\Item\Begin\BeginMiddleware;
use Romm\Formz\Middleware\Item\End\EndMiddleware;
use Romm\Formz\Middleware\MiddlewareInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\Web\Request;

/**
 * @todo: loop on middlewares and detect which ones are using signals that wont
 * be processed. This should probably be a validator put on:
 *      \Romm\Formz\Form\Definition\Form::$middlewares
 */
class MiddlewareProcessor
{
    /**
     * @var FormObject
     */
    protected $formObject;

    /**
     * @var ControllerProcessor
     */
    protected $controllerProcessor;

    /**
     * @var FormResult
     */
    protected $result;

    /**
     * @see getSignalSortedMiddlewares()
     *
     * @var array
     */
    protected $signalSortedMiddlewares = [];

    /**
     * This context is activated when the request is validating a single field
     * and not the whole form. In this case, special behaviours may occur, and
     * the processor instance should be aware of it.
     *
     * @see \Romm\Formz\Middleware\Processor\RemoveFromSingleFieldValidationContext
     *
     * @var bool
     */
    protected $singleFieldValidationContext = false;

    /**
     * @param FormObject          $formObject
     * @param ControllerProcessor $controllerProcessor
     */
    public function __construct(FormObject $formObject, ControllerProcessor $controllerProcessor)
    {
        $this->formObject = $formObject;
        $this->controllerProcessor = $controllerProcessor;
    }

    /**
     * Will run and process trough every middleware registered for the current
     * form object.
     */
    public function run()
    {
        /** @var BeginMiddleware $beginMiddleware */
        $beginMiddleware = Core::instantiate(BeginMiddleware::class);
        $beginMiddleware->bindMiddlewareProcessor($this);
        $beginMiddleware->initialize();

        foreach ($this->formObject->getDefinition()->getAllMiddlewares() as $middleware) {
            $middleware->bindMiddlewareProcessor($this);
            $middleware->initialize();
        }

        $beginMiddleware->execute();

        /** @var EndMiddleware $endMiddleware */
        $endMiddleware = Core::instantiate(EndMiddleware::class);
        $endMiddleware->bindMiddlewareProcessor($this);
        $endMiddleware->execute();
    }

    /**
     * Returns the sorted list of middlewares bound to the given signal name.
     *
     * @param string $signalName
     * @return MiddlewareInterface[]
     */
    public function getMiddlewaresBoundToSignal($signalName)
    {
        $signalSortedMiddlewares = $this->getSignalSortedMiddlewares();

        return (isset($signalSortedMiddlewares[$signalName]))
            ? $signalSortedMiddlewares[$signalName]
            : [];
    }

    /**
     * Returns a sorted list of the registered middlewares: the first level is
     * the signal used by the group, and the second level is the group of
     * middlewares, sorted by descendant priority of each middleware.
     *
     * @return array
     */
    protected function getSignalSortedMiddlewares()
    {
        if (empty($this->signalSortedMiddlewares)) {
            $this->signalSortedMiddlewares = [];
            $middlewareList = [];

            foreach ($this->getFilteredMiddlewares() as $middleware) {
                $signal = $middleware->getBoundSignalName();

                if (false === isset($middlewareList[$signal])) {
                    $middlewareList[$signal] = [];
                }

                $middlewareList[$signal][] = $middleware;
            }

            foreach ($middlewareList as $key => $list) {
                $this->signalSortedMiddlewares[$key] = $this->sortMiddlewaresListByPriority($list);
            }
        }

        return $this->signalSortedMiddlewares;
    }

    /**
     * @return MiddlewareInterface[]
     */
    protected function getFilteredMiddlewares()
    {
        $middlewares = $this->formObject->getDefinition()->getAllMiddlewares();

        if ($this->inSingleFieldValidationContext()) {
            foreach ($middlewares as $key => $middleware) {
                if ($middleware instanceof RemoveFromSingleFieldValidationContext) {
                    unset($middlewares[$key]);
                }
            }
        }

        return $middlewares;
    }

    /**
     * Will sort and return a middlewares list based on the priority of each
     * middleware. The middlewares with the highest priority will be placed at
     * the top of the list.
     *
     * @param MiddlewareInterface[] $list
     * @return MiddlewareInterface[]
     */
    private function sortMiddlewaresListByPriority(array $list)
    {
        usort($list, function (MiddlewareInterface $a, MiddlewareInterface $b) {
            $priorityA = (int)$a->getPriority();
            $priorityB = (int)$b->getPriority();

            if ($priorityA === $priorityB) {
                return 0;
            }

            return $priorityA < $priorityB ? 1 : -1;
        });

        return $list;
    }

    /**
     * @return FormObject
     */
    public function getFormObject()
    {
        return $this->formObject;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->controllerProcessor->getRequest();
    }

    /**
     * @return Arguments
     */
    public function getRequestArguments()
    {
        return $this->controllerProcessor->getRequestArguments();
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->controllerProcessor->getSettings();
    }

    /**
     * @see $singleFieldValidationContext
     *
     * @return bool
     */
    public function inSingleFieldValidationContext()
    {
        return $this->singleFieldValidationContext;
    }

    /**
     * @see $singleFieldValidationContext
     */
    public function activateSingleFieldValidationContext()
    {
        $this->singleFieldValidationContext = true;
    }
}
