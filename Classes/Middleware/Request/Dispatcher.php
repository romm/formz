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

namespace Romm\Formz\Middleware\Request;

use Romm\Formz\Form\FormObject\FormObject;
use Romm\Formz\Middleware\Request\Exception\StopPropagationException;
use TYPO3\CMS\Extbase\Mvc\Request;

/**
 * A dispatcher used to forward or redirect the current request to another
 * controller.
 */
abstract class Dispatcher
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var FormObject
     */
    protected $formObject;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var string
     */
    protected $controller;

    /**
     * @var string
     */
    protected $extension;

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * @param Request $request
     * @param FormObject $formObject
     */
    final public function __construct(Request $request, FormObject $formObject)
    {
        $this->request = $request;
        $this->formObject = $formObject;
    }

    /**
     * Dispatch implementation of this request dispatcher.
     *
     * @throws StopPropagationException
     * @return void
     */
    abstract public function dispatch();

    /**
     * @param string $action
     * @return $this
     */
    public function toAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @param string $controller
     * @return $this
     */
    public function toController($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * @param string $extension
     * @return $this
     */
    public function toExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * @param array $arguments
     * @return $this
     */
    public function withArguments(array $arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }
}
