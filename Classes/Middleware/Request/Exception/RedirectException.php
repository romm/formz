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

namespace Romm\Formz\Middleware\Request\Exception;

class RedirectException extends StopPropagationException
{
    /**
     * @var string
     */
    protected $actionName;

    /**
     * @var string
     */
    protected $controllerName;

    /**
     * @var string
     */
    protected $extensionName;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var string
     */
    protected $pageUid;

    /**
     * @var int
     */
    protected $delay;

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @param string $actionName
     * @param string $controllerName
     * @param string $extensionName
     * @param array  $arguments
     * @param string $pageUid
     * @param int    $delay
     * @param int    $statusCode
     */
    public function __construct(
        $actionName,
        $controllerName = null,
        $extensionName = null,
        array $arguments = null,
        $pageUid = null,
        $delay = 0,
        $statusCode = 303
    ) {
        $this->actionName = $actionName;
        $this->controllerName = $controllerName;
        $this->extensionName = $extensionName;
        $this->arguments = $arguments;
        $this->pageUid = $pageUid;
        $this->delay = $delay;
        $this->statusCode = $statusCode;
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * @return string
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }

    /**
     * @return string
     */
    public function getExtensionName()
    {
        return $this->extensionName;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @return string
     */
    public function getPageUid()
    {
        return $this->pageUid;
    }

    /**
     * @return int
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
