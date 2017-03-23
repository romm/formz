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

namespace Romm\Formz\Service;

use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Service\Traits\ExtendedSelfInstantiateTrait;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;

class ControllerService implements SingletonInterface
{
    use ExtendedSelfInstantiateTrait;

    /**
     * @var ReflectionService
     */
    protected $reflectionService;

    /**
     * @param string $controllerName
     * @param string $actionName
     * @return array
     */
    public function getControllerActionParameters($controllerName, $actionName)
    {
        return $this->reflectionService->getMethodParameters($controllerName, $actionName . 'Action');
    }

    /**
     * @param string $controllerName
     * @param string $actionName
     * @param string $formName
     * @return string
     * @throws EntryNotFoundException
     */
    public function getFormClassNameFromControllerAction($controllerName, $actionName, $formName)
    {
        $methodParameters = $this->getControllerActionParameters($controllerName, $actionName);

        if (false === isset($methodParameters[$formName])) {
            throw EntryNotFoundException::controllerServiceActionFormArgumentMissing($controllerName, $actionName, $formName);
        }

        return $methodParameters[$formName]['type'];
    }

    /**
     * @param ReflectionService $reflectionService
     */
    public function injectReflectionService(ReflectionService $reflectionService)
    {
        $this->reflectionService = $reflectionService;
    }
}
